<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Huella;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestión de huellas dactilares
 * 
 * Centraliza la lógica de negocio para enrollment, identificación
 * y reconciliación de huellas entre Laravel y ESP32.
 */
class FingerprintService
{
    /**
     * URL base del ESP32 con sensor AS608
     */
    private string $esp32Url;

    /**
     * Token de autenticación para ESP32
     */
    private ?string $apiToken;

    /**
     * Timeout para requests HTTP
     */
    private int $timeout;

    /**
     * Timeout para proceso de enrollment
     */
    private int $enrollTimeout;

    /**
     * Capacidad total del sensor
     */
    private int $totalSlots;

    /**
     * Calidad mínima aceptable (0-255)
     */
    private int $minQualityScore;

    public function __construct()
    {
        $this->esp32Url = config('services.esp32.fingerprint_url');
        $this->apiToken = config('services.esp32.api_token');
        $this->timeout = config('services.esp32.timeout', 30);
        $this->enrollTimeout = config('services.esp32.enroll_timeout', 60);
        $this->totalSlots = config('services.esp32.sensor.total_slots', 300);
        $this->minQualityScore = config('services.esp32.sensor.min_quality_score', 80);
    }

    /**
     * Registrar huella en la base de datos después de enrollment exitoso
     * 
     * @param int $empleadoId ID del empleado
     * @param int $slotId Slot asignado por ESP32 (0-299)
     * @param string $templateBase64 Template codificado en base64
     * @param int $qualityScore Calidad de la captura (0-255)
     * @param int|null $adminId ID del administrador que registra
     * @param string $tipoDedo Tipo de dedo usado
     * @param string $mano Mano usada
     * @return array Resultado del enrollment con huella_id y empleado actualizado
     * @throws \Exception Si falla el proceso
     */
    public function enrollFingerprint(
        int $empleadoId,
        int $slotId,
        string $templateBase64,
        int $qualityScore,
        ?int $adminId = null,
        string $tipoDedo = 'Indice',
        string $mano = 'Derecha'
    ): array {
        try {
            DB::beginTransaction();

            // Validar empleado
            $empleado = Empleado::findOrFail($empleadoId);

            if ($empleado->estado !== 'Pendiente_Huella') {
                throw new \Exception(
                    "El empleado no está en estado 'Pendiente_Huella'. Estado actual: {$empleado->estado}"
                );
            }

            // Validar slot disponible
            if ($this->isSlotOccupied($slotId)) {
                throw new \Exception("El slot {$slotId} ya está ocupado en la base de datos");
            }

            // Validar calidad mínima
            if ($qualityScore < $this->minQualityScore) {
                throw new \Exception(
                    "Calidad de huella insuficiente: {$qualityScore} (mínimo: {$this->minQualityScore})"
                );
            }

            // Decodificar template
            $templateBinary = base64_decode($templateBase64);
            if ($templateBinary === false) {
                throw new \Exception('Error al decodificar template base64');
            }

            // Crear registro de huella
            $huella = Huella::create([
                'empleado_id' => $empleadoId,
                'numero_slot' => $slotId,
                'template_huella' => $templateBinary,
                'calidad' => $qualityScore,
                'estado' => 'Activa',
                'enrolado_por' => $adminId,
                'tipo_dedo' => $tipoDedo,
                'mano' => $mano,
            ]);

            // Actualizar estado del empleado a Activo
            $empleado->estado = 'Activo';
            $empleado->save();

            DB::commit();

            Log::channel('fingerprint')->info('Huella registrada exitosamente', [
                'huella_id' => $huella->id,
                'empleado_id' => $empleado->id,
                'slot_id' => $slotId,
                'quality' => $qualityScore,
                'admin_id' => $adminId,
            ]);

            return [
                'success' => true,
                'huella_id' => $huella->id,
                'empleado_id' => $empleado->id,
                'slot_id' => $slotId,
                'estado_empleado' => $empleado->estado,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('fingerprint')->error('Error en enrollFingerprint', [
                'empleado_id' => $empleadoId,
                'slot_id' => $slotId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Rollback: Eliminar huella del sensor cuando falla guardado en Laravel
     * 
     * @param int $slotId Slot a eliminar del sensor
     * @return bool True si se eliminó correctamente
     */
    public function rollbackEnrollment(int $slotId): bool
    {
        try {
            // Intentar eliminar del sensor vía API
            $response = Http::timeout($this->timeout)
                ->delete("{$this->esp32Url}/fingerprint/delete-slot", [
                    'slot_id' => $slotId,
                ]);

            if ($response->successful()) {
                Log::channel('fingerprint')->info('Rollback exitoso en sensor', [
                    'slot_id' => $slotId,
                ]);
                return true;
            }

            Log::channel('fingerprint')->warning('Rollback falló en sensor', [
                'slot_id' => $slotId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error en rollbackEnrollment', [
                'slot_id' => $slotId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verificar si un slot está ocupado en la base de datos
     * 
     * @param int $slotId Slot a verificar (0-299)
     * @return bool True si está ocupado
     */
    public function isSlotOccupied(int $slotId): bool
    {
        return Huella::where('numero_slot', $slotId)
            ->where('estado', 'Activa')
            ->exists();
    }

    /**
     * Obtener todos los slots ocupados en la base de datos
     * 
     * @return array Array de slot IDs ocupados
     */
    public function getUsedSlots(): array
    {
        return Huella::where('estado', 'Activa')
            ->pluck('numero_slot')
            ->toArray();
    }

    /**
     * Obtener primer slot disponible
     * 
     * @return int|null Slot ID disponible o null si está lleno
     */
    public function getAvailableSlot(): ?int
    {
        $usedSlots = $this->getUsedSlots();

        for ($i = 0; $i < $this->totalSlots; $i++) {
            if (!in_array($i, $usedSlots)) {
                return $i;
            }
        }

        return null; // Sensor lleno
    }

    /**
     * Detectar huellas huérfanas: en sensor pero no en DB
     * 
     * Requiere que ESP32 exponga endpoint GET /fingerprint/info
     * 
     * @return array Lista de slots huérfanos
     */
    public function findHuerfanas(): array
    {
        try {
            // Obtener slots del sensor
            $response = Http::timeout($this->timeout)
                ->get("{$this->esp32Url}/fingerprint/info");

            if (!$response->successful()) {
                throw new \Exception('No se pudo conectar con ESP32');
            }

            $sensorData = $response->json();
            $slotsEnSensor = $sensorData['used_slot_ids'] ?? [];
            $slotsEnDB = $this->getUsedSlots();

            // Huellas en sensor pero no en DB
            $huerfanas = array_diff($slotsEnSensor, $slotsEnDB);

            if (!empty($huerfanas)) {
                Log::channel('fingerprint')->warning('Huellas huérfanas detectadas', [
                    'count' => count($huerfanas),
                    'slots' => $huerfanas,
                ]);
            }

            return array_values($huerfanas);

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error al detectar huérfanas', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Detectar huellas fantasma: en DB pero no en sensor
     * 
     * @return array Lista de huellas fantasma con datos completos
     */
    public function findFantasmas(): array
    {
        try {
            // Obtener slots del sensor
            $response = Http::timeout($this->timeout)
                ->get("{$this->esp32Url}/fingerprint/info");

            if (!$response->successful()) {
                throw new \Exception('No se pudo conectar con ESP32');
            }

            $sensorData = $response->json();
            $slotsEnSensor = $sensorData['used_slot_ids'] ?? [];
            $slotsEnDB = $this->getUsedSlots();

            // Huellas en DB pero no en sensor
            $slotsFantasma = array_diff($slotsEnDB, $slotsEnSensor);

            if (empty($slotsFantasma)) {
                return [];
            }

            // Obtener datos completos de huellas fantasma
            $fantasmas = Huella::with('empleado')
                ->whereIn('numero_slot', $slotsFantasma)
                ->where('estado', 'Activa')
                ->get()
                ->map(function ($huella) {
                    return [
                        'huella_id' => $huella->id,
                        'slot_id' => $huella->numero_slot,
                        'empleado_id' => $huella->empleado_id,
                        'empleado_nombre' => $huella->empleado->nombre_completo ?? 'N/A',
                        'empleado_cedula' => $huella->empleado->cedula ?? 'N/A',
                        'fecha_enrolamiento' => $huella->created_at->format('Y-m-d H:i:s'),
                    ];
                })
                ->toArray();

            Log::channel('fingerprint')->warning('Huellas fantasma detectadas', [
                'count' => count($fantasmas),
                'slots' => $slotsFantasma,
            ]);

            return $fantasmas;

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error al detectar fantasmas', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Limpiar huellas huérfanas del sensor
     * 
     * @return array Resultado con slots eliminados
     */
    public function cleanHuerfanas(): array
    {
        $huerfanas = $this->findHuerfanas();

        if (empty($huerfanas)) {
            return [
                'success' => true,
                'deleted_count' => 0,
                'message' => 'No hay huellas huérfanas',
            ];
        }

        $deletedSlots = [];
        $failedSlots = [];

        foreach ($huerfanas as $slotId) {
            if ($this->rollbackEnrollment($slotId)) {
                $deletedSlots[] = $slotId;
            } else {
                $failedSlots[] = $slotId;
            }
        }

        Log::channel('fingerprint')->info('Limpieza de huérfanas completada', [
            'total_found' => count($huerfanas),
            'deleted' => count($deletedSlots),
            'failed' => count($failedSlots),
        ]);

        return [
            'success' => empty($failedSlots),
            'deleted_count' => count($deletedSlots),
            'deleted_slots' => $deletedSlots,
            'failed_slots' => $failedSlots,
            'message' => sprintf(
                'Eliminadas %d de %d huellas huérfanas',
                count($deletedSlots),
                count($huerfanas)
            ),
        ];
    }

    /**
     * Marcar huellas fantasma como perdidas
     * 
     * @return array Resultado con huellas marcadas
     */
    public function markFantasmasAsLost(): array
    {
        $fantasmas = $this->findFantasmas();

        if (empty($fantasmas)) {
            return [
                'success' => true,
                'updated_count' => 0,
                'message' => 'No hay huellas fantasma',
            ];
        }

        try {
            DB::beginTransaction();

            $slotIds = array_column($fantasmas, 'slot_id');

            // Marcar como Inactiva en lugar de eliminar
            $updatedCount = Huella::whereIn('numero_slot', $slotIds)
                ->where('estado', 'Activa')
                ->update(['estado' => 'Inactiva']);

            // Revertir empleados a Pendiente_Huella
            $empleadoIds = array_column($fantasmas, 'empleado_id');
            Empleado::whereIn('id', $empleadoIds)
                ->where('estado', 'Activo')
                ->update(['estado' => 'Pendiente_Huella']);

            DB::commit();

            Log::channel('fingerprint')->info('Huellas fantasma marcadas como perdidas', [
                'updated_count' => $updatedCount,
                'slot_ids' => $slotIds,
            ]);

            return [
                'success' => true,
                'updated_count' => $updatedCount,
                'affected_slots' => $slotIds,
                'message' => sprintf('Marcadas %d huellas como perdidas', $updatedCount),
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('fingerprint')->error('Error al marcar fantasmas', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'updated_count' => 0,
                'message' => 'Error al marcar huellas fantasma: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verificar conectividad con ESP32
     * 
     * @return array Estado de la conexión
     */
    public function checkEsp32Connection(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->esp32Url}/fingerprint/info");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'connected' => true,
                    'total_slots' => $data['total_slots'] ?? $this->totalSlots,
                    'used_slots' => $data['used_slots'] ?? 0,
                    'available_slots' => $data['available_slots'] ?? $this->totalSlots,
                    'message' => 'ESP32 conectado correctamente',
                ];
            }

            return [
                'connected' => false,
                'message' => 'ESP32 no responde (HTTP ' . $response->status() . ')',
            ];

        } catch (\Exception $e) {
            return [
                'connected' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener estadísticas del sistema de huellas
     * 
     * @return array Estadísticas completas
     */
    public function getStatistics(): array
    {
        $usedSlots = $this->getUsedSlots();
        $totalEmpleados = Empleado::count();
        $empleadosConHuella = Empleado::activosConHuella()->count();
        $empleadosPendientes = Empleado::pendientesHuella()->count();

        return [
            'sensor' => [
                'total_slots' => $this->totalSlots,
                'used_slots' => count($usedSlots),
                'available_slots' => $this->totalSlots - count($usedSlots),
                'usage_percentage' => round((count($usedSlots) / $this->totalSlots) * 100, 2),
            ],
            'empleados' => [
                'total' => $totalEmpleados,
                'con_huella' => $empleadosConHuella,
                'pendientes' => $empleadosPendientes,
                'completion_percentage' => $totalEmpleados > 0
                    ? round(($empleadosConHuella / $totalEmpleados) * 100, 2)
                    : 0,
            ],
            'huellas' => [
                'activas' => Huella::where('estado', 'Activa')->count(),
                'inactivas' => Huella::where('estado', 'Inactiva')->count(),
                'bloqueadas' => Huella::where('estado', 'Bloqueada')->count(),
            ],
        ];
    }
}
