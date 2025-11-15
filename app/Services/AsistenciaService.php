<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Empleado;
use App\Models\Horario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestionar la lógica compleja de asistencias
 * Calcula estados, valida horarios, detecta ausencias
 */
class AsistenciaService
{
    /**
     * Registrar una asistencia con validación automática
     */
    public function registrarAsistencia(
        int $empleadoId,
        string $tipo = 'Entrada',
        ?int $huellaId = null,
        string $metodoRegistro = 'Huella',
        ?string $observaciones = null
    ): Asistencia {
        $empleado = Empleado::with('horario')->findOrFail($empleadoId);
        
        if (!$empleado->horario) {
            throw new \Exception('El empleado no tiene un horario asignado');
        }

        $fecha = Carbon::now('America/Bogota')->toDateString();
        $horaRegistro = Carbon::now('America/Bogota');
        $horario = $empleado->horario;

        // Calcular estado y minutos de diferencia
        $resultado = $this->calcularEstadoAsistencia(
            $tipo,
            $horaRegistro,
            $horario,
            $fecha
        );

        $asistencia = Asistencia::create([
            'empleado_id' => $empleadoId,
            'horario_id' => $horario->id,
            'fecha' => $fecha,
            'hora_registro' => $horaRegistro,
            'huella_id' => $huellaId,
            'estado' => $resultado['estado'],
            'tipo' => $tipo,
            'minutos_diferencia' => $resultado['minutos_diferencia'],
            'observaciones' => $observaciones,
            'justificada' => false,
            'metodo_registro' => $metodoRegistro,
            'fecha_hora_registro' => $horaRegistro,
        ]);

        Log::info("Asistencia registrada", [
            'asistencia_id' => $asistencia->id,
            'empleado' => $empleado->primer_nombre . ' ' . $empleado->primer_apellido,
            'tipo' => $tipo,
            'estado' => $resultado['estado'],
            'minutos_diferencia' => $resultado['minutos_diferencia'],
        ]);

        return $asistencia;
    }

    /**
     * Calcular el estado de una asistencia y los minutos de diferencia
     */
    public function calcularEstadoAsistencia(
        string $tipo,
        Carbon $horaRegistro,
        Horario $horario,
        string $fecha
    ): array {
        $diaSemana = strtolower(Carbon::parse($fecha)->locale('es')->dayName);
        $diasLaborables = $horario->dias_laborables ?? [];

        // Verificar si es día laborable
        if (!isset($diasLaborables[$diaSemana]) || !$diasLaborables[$diaSemana]) {
            return [
                'estado' => 'Puntual', // No es día laborable, se marca como puntual
                'minutos_diferencia' => 0,
            ];
        }

        if ($tipo === 'Entrada') {
            return $this->calcularEstadoEntrada($horaRegistro, $horario);
        } else {
            return $this->calcularEstadoSalida($horaRegistro, $horario);
        }
    }

    /**
     * Calcular estado para entrada
     */
    private function calcularEstadoEntrada(Carbon $horaRegistro, Horario $horario): array
    {
        $horaEsperada = Carbon::parse($horario->hora_entrada);
        $horaLimite = $horaEsperada->copy()->addMinutes($horario->tolerancia_entrada);

        // Crear Carbon con solo la hora para comparar
        $horaRegistroSolo = Carbon::createFromTime(
            $horaRegistro->hour,
            $horaRegistro->minute,
            $horaRegistro->second
        );

        $minutosDiferencia = $horaRegistroSolo->diffInMinutes($horaEsperada, false);

        if ($horaRegistroSolo->lte($horaLimite)) {
            return [
                'estado' => 'Puntual',
                'minutos_diferencia' => max(0, $minutosDiferencia),
            ];
        } else {
            return [
                'estado' => 'Tarde',
                'minutos_diferencia' => $minutosDiferencia,
            ];
        }
    }

    /**
     * Calcular estado para salida
     */
    private function calcularEstadoSalida(Carbon $horaRegistro, Horario $horario): array
    {
        $horaEsperada = Carbon::parse($horario->hora_salida);
        $horaMinima = $horaEsperada->copy()->subMinutes($horario->tolerancia_salida);

        $horaRegistroSolo = Carbon::createFromTime(
            $horaRegistro->hour,
            $horaRegistro->minute,
            $horaRegistro->second
        );

        $minutosDiferencia = $horaRegistroSolo->diffInMinutes($horaEsperada, false);

        if ($horaRegistroSolo->gte($horaMinima)) {
            return [
                'estado' => 'Puntual',
                'minutos_diferencia' => abs($minutosDiferencia),
            ];
        } else {
            return [
                'estado' => 'Tarde', // Salida anticipada
                'minutos_diferencia' => abs($minutosDiferencia),
            ];
        }
    }

    /**
     * Justificar una asistencia
     */
    public function justificarAsistencia(
        int $asistenciaId,
        string $justificacion,
        int $adminId
    ): Asistencia {
        $asistencia = Asistencia::findOrFail($asistenciaId);

        // Solo se puede justificar si el estado es Tarde o Ausente
        if (!in_array($asistencia->estado, ['Tarde', 'Ausente'])) {
            throw new \Exception('Solo se pueden justificar asistencias con estado Tarde o Ausente');
        }

        // Guardar justificación SIN cambiar el estado
        $asistencia->update([
            'justificada' => true,
            'motivo_justificacion' => $justificacion,
            'justificado_por' => $adminId,
            'fecha_justificacion' => Carbon::now('America/Bogota'),
            // NO se modifica el estado
        ]);

        Log::info("Asistencia justificada", [
            'asistencia_id' => $asistenciaId,
            'admin_id' => $adminId,
            'motivo' => $justificacion,
            'estado_actual' => $asistencia->estado,
        ]);

        return $asistencia->fresh();
    }

    /**
     * Detectar empleados ausentes en una fecha específica
     */
    public function detectarAusencias(string $fecha = null): array
    {
        $fecha = $fecha ?? Carbon::now('America/Bogota')->toDateString();
        $diaSemana = strtolower(Carbon::parse($fecha)->locale('es')->dayName);

        Log::info("Iniciando detección de ausencias", ['fecha' => $fecha, 'dia' => $diaSemana]);

        // Obtener empleados activos con horario asignado
        $empleados = Empleado::where('estado', 'Activo')
            ->whereNotNull('horario_id')
            ->with('horario')
            ->get();

        $ausencias = [];

        foreach ($empleados as $empleado) {
            $horario = $empleado->horario;
            
            if (!$horario) {
                continue;
            }

            $diasLaborables = $horario->dias_laborables ?? [];

            // Verificar si es día laborable
            if (!isset($diasLaborables[$diaSemana]) || !$diasLaborables[$diaSemana]) {
                continue; // No es día laborable
            }

            // Verificar si ya tiene registro de entrada
            $tieneEntrada = Asistencia::where('empleado_id', $empleado->id)
                ->where('fecha', $fecha)
                ->exists();

            if (!$tieneEntrada) {
                // Crear registro de ausencia
                $asistencia = Asistencia::create([
                    'empleado_id' => $empleado->id,
                    'horario_id' => $horario->id,
                    'fecha' => $fecha,
                    'hora_registro' => Carbon::parse($horario->hora_entrada)->format('H:i:s'),
                    'huella_id' => null,
                    'estado' => 'Ausente',
                    'tipo' => 'Entrada',
                    'minutos_diferencia' => 0,
                    'justificada' => false,
                    'metodo_registro' => 'Manual',
                    'fecha_hora_registro' => Carbon::now('America/Bogota'),
                ]);

                $ausencias[] = [
                    'empleado' => $empleado,
                    'asistencia' => $asistencia,
                ];

                Log::info("Ausencia detectada", [
                    'empleado_id' => $empleado->id,
                    'nombre' => $empleado->primer_nombre . ' ' . $empleado->primer_apellido,
                    'fecha' => $fecha,
                ]);
            }
        }

        Log::info("Detección de ausencias completada", [
            'total_ausencias' => count($ausencias),
            'fecha' => $fecha,
        ]);

        return $ausencias;
    }

    /**
     * Obtener estadísticas de asistencias por fecha
     */
    public function obtenerEstadisticas(string $fecha = null): array
    {
        $fecha = $fecha ?? Carbon::now('America/Bogota')->toDateString();

        $asistencias = Asistencia::where('fecha', $fecha)->get();

        return [
            'total' => $asistencias->count(),
            'puntuales' => $asistencias->where('estado', 'Puntual')->count(),
            'tarde' => $asistencias->where('estado', 'Tarde')->count(),
            'ausentes' => $asistencias->where('estado', 'Ausente')->count(),
            'justificados' => $asistencias->where('justificado', true)->count(),
            'fecha' => $fecha,
        ];
    }

    /**
     * Verificar si un empleado ya marcó entrada/salida hoy
     */
    public function yaMarcoHoy(int $empleadoId, string $tipo = 'Entrada'): bool
    {
        $fecha = Carbon::now('America/Bogota')->toDateString();

        return Asistencia::where('empleado_id', $empleadoId)
            ->where('fecha', $fecha)
            ->where('tipo', $tipo)
            ->exists();
    }
}
