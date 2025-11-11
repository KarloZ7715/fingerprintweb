<?php

namespace App\Jobs;

use App\Models\Empleado;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job para detectar empleados en estado Pendiente_Huella por más de 24h
 * 
 * Se ejecuta diariamente mediante el scheduler de Laravel.
 * Envía notificaciones a administradores por Filament.
 */
class DetectPendingEmployees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tiempo máximo permitido en estado Pendiente_Huella (horas)
     */
    private int $maxPendingHours = 24;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('fingerprint')->info('Iniciando detección de empleados pendientes');

        try {
            // Buscar empleados pendientes por más de 24 horas
            $pendingEmployees = Empleado::where('estado', 'Pendiente_Huella')
                ->where('created_at', '<', now()->subHours($this->maxPendingHours))
                ->orderBy('created_at', 'asc')
                ->get();

            if ($pendingEmployees->isEmpty()) {
                Log::channel('fingerprint')->info('No hay empleados pendientes por más de 24h');
                return;
            }

            $count = $pendingEmployees->count();

            // Preparar lista de empleados para notificación
            $employeeList = $pendingEmployees->map(function ($empleado) {
                $horasPendiente = now()->diffInHours($empleado->created_at);
                return "• {$empleado->nombre} {$empleado->apellido} (CI: {$empleado->cedula}) - {$horasPendiente}h pendiente";
            })->join("\n");

            // Enviar notificación a todos los administradores
            Notification::make()
                ->warning()
                ->title('Empleados con huella pendiente')
                ->body("Hay {$count} empleado(s) en estado Pendiente_Huella por más de 24 horas:\n\n{$employeeList}")
                ->icon('heroicon-o-exclamation-triangle')
                ->persistent() // No se cierra automáticamente
                ->sendToDatabase(\App\Models\Administrador::all());

            Log::channel('fingerprint')->warning('Empleados pendientes detectados', [
                'count' => $count,
                'empleados' => $pendingEmployees->pluck('id')->toArray(),
            ]);

            // Opcional: Auto-eliminar empleados pendientes por más de 7 días
            // (Comentado por defecto, descomentar si se desea esta funcionalidad)
            /*
            $oldPendingEmployees = Empleado::where('estado', 'Pendiente_Huella')
                ->where('created_at', '<', now()->subDays(7))
                ->get();

            if ($oldPendingEmployees->isNotEmpty()) {
                foreach ($oldPendingEmployees as $empleado) {
                    Log::channel('fingerprint')->info('Auto-eliminando empleado pendiente >7 días', [
                        'empleado_id' => $empleado->id,
                        'nombre' => $empleado->nombre_completo,
                        'dias_pendiente' => now()->diffInDays($empleado->created_at),
                    ]);

                    $empleado->delete();
                }

                $deletedCount = $oldPendingEmployees->count();

                Notification::make()
                    ->danger()
                    ->title('Empleados pendientes eliminados automáticamente')
                    ->body("{$deletedCount} empleado(s) en estado Pendiente_Huella por más de 7 días fueron eliminados del sistema.")
                    ->icon('heroicon-o-trash')
                    ->sendToDatabase(\App\Models\Administrador::all());
            }
            */

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error en DetectPendingEmployees', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Notificar error a administradores
            Notification::make()
                ->danger()
                ->title('Error en detección de empleados pendientes')
                ->body('Ocurrió un error al buscar empleados con huella pendiente. Revisar logs.')
                ->icon('heroicon-o-x-circle')
                ->sendToDatabase(\App\Models\Administrador::all());
        }
    }
}
