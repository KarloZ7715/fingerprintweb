<?php

namespace App\Console\Commands;

use App\Services\AsistenciaService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DetectarAusenciasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asistencias:detectar-ausencias 
                            {fecha? : Fecha en formato Y-m-d (opcional, por defecto hoy)}
                            {--no-email : No enviar email de reporte}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detecta empleados ausentes y genera reporte';

    /**
     * Execute the console command.
     */
    public function handle(AsistenciaService $service): int
    {
        $fecha = $this->argument('fecha') ?? Carbon::now('America/Bogota')->toDateString();
        $enviarEmail = !$this->option('no-email');

        $this->info("🔍 Detectando ausencias para: {$fecha}");
        $this->newLine();

        try {
            // Detectar ausencias
            $ausencias = $service->detectarAusencias($fecha);

            if (count($ausencias) > 0) {
                $this->warn("⚠️  Se detectaron " . count($ausencias) . " ausencias:");
                $this->newLine();

                $this->table(
                    ['Empleado', 'Cédula', 'Horario'],
                    collect($ausencias)->map(function ($item) {
                        $empleado = $item['empleado'];
                        return [
                            $empleado->primer_nombre . ' ' . $empleado->primer_apellido,
                            $empleado->cedula,
                            $empleado->horario->nombre ?? 'N/A',
                        ];
                    })->toArray()
                );
            } else {
                $this->success("✅ No se detectaron ausencias");
            }

            // Obtener estadísticas
            $stats = $service->obtenerEstadisticas($fecha);
            $this->newLine();
            $this->info("📊 Estadísticas del día:");
            $this->line("   Total: {$stats['total']}");
            $this->line("   Puntuales: {$stats['puntuales']}");
            $this->line("   Tarde: {$stats['tarde']}");
            $this->line("   Ausentes: {$stats['ausentes']}");
            $this->line("   Justificados: {$stats['justificados']}");

            if ($enviarEmail) {
                $this->newLine();
                $this->info("📧 El reporte será enviado por email...");
            }

            $this->newLine();
            $this->success("✅ Proceso completado exitosamente");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
