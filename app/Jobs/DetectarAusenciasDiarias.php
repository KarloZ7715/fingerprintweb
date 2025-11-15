<?php

namespace App\Jobs;

use App\Services\AsistenciaService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job que se ejecuta al final del día para detectar empleados ausentes
 */
class DetectarAusenciasDiarias implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fecha;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $fecha = null)
    {
        $this->fecha = $fecha ?? Carbon::now('America/Bogota')->toDateString();
    }

    /**
     * Execute the job.
     */
    public function handle(AsistenciaService $asistenciaService): void
    {
        Log::info("Job DetectarAusenciasDiarias iniciado", [
            'fecha' => $this->fecha,
        ]);

        try {
            // Detectar ausencias
            $ausencias = $asistenciaService->detectarAusencias($this->fecha);
            
            Log::info("Job DetectarAusenciasDiarias completado exitosamente", [
                'fecha' => $this->fecha,
                'ausencias_detectadas' => count($ausencias),
            ]);

        } catch (\Exception $e) {
            Log::error("Error en Job DetectarAusenciasDiarias", [
                'fecha' => $this->fecha,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job DetectarAusenciasDiarias falló", [
            'fecha' => $this->fecha,
            'error' => $exception->getMessage(),
        ]);
    }
}
