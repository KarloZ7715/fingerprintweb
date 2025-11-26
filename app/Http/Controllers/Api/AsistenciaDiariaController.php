<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistroSensor;
use App\Models\Huella;
use App\Models\AsistenciaDiaria;
use Illuminate\Database\QueryException;
use Carbon\Carbon;

class AsistenciaDiariaController extends Controller
{
    public function store($huella_id)
    {
        try {
            // Insertar registro sin verificar existencia previa
            $registro = RegistroSensor::create([
                'huella_id'  => $huella_id,
                'fecha_hora' => Carbon::now(),
            ]);

            // Obtener info detallada de la asistencia tras ejecución del trigger
            $huella = Huella::with('empleado')->findOrFail($huella_id);
            $empleado = $huella->empleado;
            
            $asistencia = AsistenciaDiaria::where('empleado_id', $empleado->id)
                ->whereDate('fecha', Carbon::today())
                ->first();
            
            if (!$asistencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener información de asistencia',
                ], 500);
            }
            
            // Determinar tipo (entrada o salida)
            $tipo = $asistencia->hora_salida ? 'salida' : 'entrada';
            
            // Determinar estado basado en minutos_retraso y estado del registro
            $estado = 'puntual';
            if ($tipo === 'entrada' && $asistencia->minutos_retraso > 0) {
                $estado = 'tarde';
            }
            if ($asistencia->estado === 'completo') {
                $estado = 'completo';
            }
            
            // Formatear hora con verificación de null
            $hora = null;
            if ($tipo === 'entrada' && $asistencia->hora_entrada) {
                $hora = $asistencia->hora_entrada->format('H:i:s');
            } elseif ($tipo === 'salida' && $asistencia->hora_salida) {
                $hora = $asistencia->hora_salida->format('H:i:s');
            }

            return response()->json([
                'success' => true,
                'message' => 'Asistencia registrada correctamente',
                'empleado_id' => $empleado->id,
                'nombre_completo' => $empleado->nombre_completo,
                'tipo' => $tipo,
                'hora' => $hora,
                'estado' => $estado,
                'minutos_retraso' => $asistencia->minutos_retraso,
                'horas_trabajadas' => $asistencia->horas_trabajadas ?? 0,
            ], 201);

        } catch (QueryException $e) {
            // Si el trigger lanza SQLSTATE '45000', atrapa el mensaje y responde
            $sqlMessage = $e->getMessage();

            // Opcional: cambia el mensaje si detecta tu trigger
            if (strpos($sqlMessage, '45000') !== false) {
                if (strpos($sqlMessage, 'Ya completó entrada y salida hoy') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya preparado entrada y salida hoy',
                    ], 409);
                }
                if (strpos($sqlMessage, 'Sin horario asignado') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sin horario asignado',
                    ], 409);
                }
                // Puedes agregar más condiciones si tu trigger lanza otros mensajes
            }

            // Otros errores SQL
            return response()->json([
                'success' => false,
                'message' => 'Error en la base de datos: ' . $sqlMessage,
            ], 500);
        }
    }
}