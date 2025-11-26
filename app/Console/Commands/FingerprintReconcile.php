<?php

namespace App\Console\Commands;

use App\Services\FingerprintService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Comando para reconciliar estado entre sensor AS608 y base de datos
 * 
 * Detecta y corrige:
 * - Huellas huérfanas: en sensor pero no en DB (elimina del sensor)
 * - Huellas fantasma: en DB pero no en sensor (marca como Perdida en DB)
 * 
 * Uso:
 *   php artisan fingerprint:reconcile --dry-run  # Ver reporte sin ejecutar
 *   php artisan fingerprint:reconcile --force    # Ejecutar limpieza
 */
class FingerprintReconcile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fingerprint:reconcile 
                            {--dry-run : Mostrar qué se haría sin ejecutar} 
                            {--force : Ejecutar limpieza sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconciliar huellas entre sensor AS608 y base de datos MySQL';

    /**
     * Servicio de gestión de huellas
     */
    private FingerprintService $fingerprintService;

    /**
     * Constructor
     */
    public function __construct(FingerprintService $fingerprintService)
    {
        parent::__construct();
        $this->fingerprintService = $fingerprintService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando reconciliación de huellas...');
        $this->newLine();

        try {
            // 1. Verificar conectividad con ESP32
            $this->line('[1/4] Verificando conexión con ESP32...');
            $connection = $this->fingerprintService->checkEsp32Connection();

            if (!$connection['connected']) {
                $this->error('ERROR: ' . $connection['message']);
                return Command::FAILURE;
            }

            $this->info('OK - ESP32 conectado');
            $this->line("   Total slots: {$connection['total_slots']}");
            $this->line("   Slots ocupados: {$connection['used_slots']}");
            $this->newLine();

            // 2. Detectar huellas huérfanas
            $this->line('[2/4] Detectando huellas huérfanas (en sensor pero no en DB)...');
            $huerfanas = $this->fingerprintService->findHuerfanas();

            if (empty($huerfanas)) {
                $this->info('OK - No se encontraron huellas huérfanas');
            } else {
                $this->warn('ADVERTENCIA: Encontradas ' . count($huerfanas) . ' huellas huérfanas');
                $this->table(['Slot ID'], array_map(fn($slot) => [$slot], $huerfanas));
            }
            $this->newLine();

            // 3. Detectar huellas fantasma
            $this->line('[3/4] Detectando huellas fantasma (en DB pero no en sensor)...');
            $fantasmas = $this->fingerprintService->findFantasmas();

            if (empty($fantasmas)) {
                $this->info('OK - No se encontraron huellas fantasma');
            } else {
                $this->warn('ADVERTENCIA: Encontradas ' . count($fantasmas) . ' huellas fantasma');
                $this->table(
                    ['Huella ID', 'Slot ID', 'Empleado', 'Cédula', 'Fecha Enrolamiento'],
                    array_map(fn($f) => [
                        $f['huella_id'],
                        $f['slot_id'],
                        $f['empleado_nombre'],
                        $f['empleado_cedula'],
                        $f['fecha_enrolamiento'],
                    ], $fantasmas)
                );
            }
            $this->newLine();

            // 4. Mostrar resumen
            $this->line('[4/4] Resumen de Reconciliación');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line("Huellas huérfanas: " . count($huerfanas) . " (se eliminarán del sensor)");
            $this->line("Huellas fantasma: " . count($fantasmas) . " (se marcarán como Perdida en DB)");
            $this->newLine();

            // 5. Si es dry-run, terminar aquí
            if ($this->option('dry-run')) {
                $this->info('Modo dry-run: No se ejecutaron cambios');
                return Command::SUCCESS;
            }

            // 6. Si no hay nada que hacer, terminar
            if (empty($huerfanas) && empty($fantasmas)) {
                $this->info('OK - No hay inconsistencias que corregir');
                return Command::SUCCESS;
            }

            // 7. Confirmar ejecución (si no es --force)
            if (!$this->option('force')) {
                if (!$this->confirm('¿Desea ejecutar la reconciliación?', false)) {
                    $this->warn('CANCELADO: Reconciliación cancelada por el usuario');
                    return Command::SUCCESS;
                }
            }

            // 8. Ejecutar limpieza de huérfanas
            if (!empty($huerfanas)) {
                $this->line('Eliminando huellas huérfanas del sensor...');
                $result = $this->fingerprintService->cleanHuerfanas();

                if ($result['success']) {
                    $this->info("OK - {$result['message']}");
                } else {
                    $this->error("ERROR: {$result['message']}");
                    if (!empty($result['failed_slots'])) {
                        $this->warn('   Slots que fallaron: ' . implode(', ', $result['failed_slots']));
                    }
                }
                $this->newLine();
            }

            // 9. Ejecutar marcado de fantasmas
            if (!empty($fantasmas)) {
                $this->line('Marcando huellas fantasma como perdidas...');
                $result = $this->fingerprintService->markFantasmasAsLost();

                if ($result['success']) {
                    $this->info("OK - {$result['message']}");
                } else {
                    $this->error("ERROR: {$result['message']}");
                }
                $this->newLine();
            }

            // 10. Log final
            Log::channel('fingerprint')->info('Reconciliación completada', [
                'huerfanas_encontradas' => count($huerfanas),
                'fantasmas_encontradas' => count($fantasmas),
                'modo' => $this->option('dry-run') ? 'dry-run' : 'ejecución',
            ]);

            $this->info('Reconciliación completada exitosamente');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('ERROR: ' . $e->getMessage());
            Log::channel('fingerprint')->error('Error en reconciliación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
