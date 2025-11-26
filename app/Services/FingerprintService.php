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

    /**
     * Servicio de discovery del ESP32 (DESACTIVADO - usar IP directa del .env)
     * 
     * NOTA: Discovery causaba timeout de 120+ segundos al escanear red completa
     * cuando mDNS no resolvía. Ahora usamos solo ESP32_URL del .env.
     */
    // private ESP32DiscoveryService $discoveryService;

    public function __construct()
    {
        // $this->discoveryService = $discoveryService; // DESACTIVADO
        $this->esp32Url = $this->getESP32Url();
        $this->apiToken = null; // Por ahora sin autenticación
        $this->timeout = config('fingerprint.sensor.timeout', 10);
        $this->enrollTimeout = config('fingerprint.sensor.timeout', 10) * 6; // 60 segundos para enrollment
        $this->totalSlots = config('fingerprint.sensor.capacity', 300);
        $this->minQualityScore = config('fingerprint.enrollment.quality_threshold', 100);
    }

    /**
     * Obtiene la URL del ESP32 desde .env (discovery desactivado)
     * 
     * @return string URL del ESP32
     * @throws \Exception Si no está configurado en .env
     */
    private function getESP32Url(): string
    {
        // Usar SOLO la IP configurada en .env (ESP32_URL)
        // Discovery desactivado para evitar timeout por escaneo de red (120+ segundos)
        $url = config('fingerprint.esp32_url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception(
                'ESP32_URL no configurado correctamente en .env. ' .
                'Formato esperado: ESP32_URL=http://IP_DEL_ESP32 (ejemplo: ESP32_URL=http://192.168.1.29)'
            );
        }

        logger()->debug('[FingerprintService] Usando URL de .env', ['url' => $url]);
        return rtrim($url, '/'); // Remover trailing slash
    }

    /**
     * Registrar huella en la base de datos después de enrollment exitoso
     * 
     * @param int $empleadoId ID del empleado
     * @param int $slotId Slot asignado por ESP32 (0-299)
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
        int $qualityScore, // Ignorado en modo asíncrono
        ?int $adminId = null,
        string $tipoDedo = 'Indice',
        string $mano = 'Derecha'
    ): array {
        // En lugar de llamar a la ESP32 directamente, creamos un comando pendiente
        // La ESP32 consultará este comando mediante polling
        
        // Validar empleado solo si el ID es mayor a 0 (para permitir enrollment anónimo en wizard)
        if ($empleadoId > 0) {
            $empleado = Empleado::findOrFail($empleadoId);
        }

        // Crear comando
        $command = \App\Models\DeviceCommand::create([
            'command' => 'enroll_fingerprint',
            'payload' => [
                'empleado_id' => $empleadoId,
                'slot_id' => $slotId,
                'admin_id' => $adminId,
                'tipo_dedo' => $tipoDedo,
                'mano' => $mano
            ],
            'status' => 'pending'
        ]);

        Log::channel('fingerprint')->info('Comando de enrollment encolado', [
            'command_id' => $command->id,
            'empleado_id' => $empleadoId,
            'slot_id' => $slotId
        ]);

        return [
            'success' => true,
            'queued' => true,
            'command_id' => $command->id,
            'message' => 'Comando enviado al dispositivo. Esperando ejecución...'
        ];
    }

    /**
     * Obtener comandos pendientes para la ESP32
     */
    public function getPendingCommands()
    {
        return \App\Models\DeviceCommand::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Actualizar estado de un comando (llamado por ESP32)
     */
    public function updateCommandStatus($id, $status, $result = null)
    {
        $command = \App\Models\DeviceCommand::findOrFail($id);
        $command->status = $status;
        if ($result) {
            $command->result = $result;
        }
        $command->save();

        // Si el comando se completó exitosamente y era un enrollment, registrar la huella
        if ($status === 'completed' && $command->command === 'enroll_fingerprint') {
            $this->finalizeEnrollment($command);
        }

        return $command;
    }

    /**
     * Finalizar enrollment (Guardar en BD) tras confirmación de ESP32
     */
    /**
     * Obtener estado de un comando
     */
    public function getCommandStatus($id)
    {
        return \App\Models\DeviceCommand::find($id);
    }

    /**
     * Cancelar un comando pendiente
     */
    public function cancelCommand($id)
    {
        $command = \App\Models\DeviceCommand::find($id);
        if ($command && $command->status === 'pending') {
            $command->status = 'failed';
            $command->result = ['error' => 'Cancelado por el usuario'];
            $command->save();
            return true;
        }
        return false;
    }
    /**
     * Finalizar enrollment (Guardar en BD) tras confirmación de ESP32
     */
    private function finalizeEnrollment($command)
    {
        $payload = $command->payload;
        $result = $command->result;

        // Extraer datos
        $empleadoId = $payload['empleado_id'];
        $slotId = $payload['slot_id'];
        $qualityScore = $result['quality_score'] ?? 100;
        $adminId = $payload['admin_id'] ?? null;
        $tipoDedo = $payload['tipo_dedo'] ?? 'Indice';
        $mano = $payload['mano'] ?? 'Derecha';

        try {
            DB::beginTransaction();

            // Si el empleadoId es 0, es un enrollment anónimo (desde wizard de creación)
            // No guardamos la huella aquí, el wizard lo hará manualmente usando el resultado del comando
            if ($empleadoId === 0) {
                Log::channel('fingerprint')->info('Enrollment anónimo completado (Wizard)', [
                    'command_id' => $command->id,
                    'slot_id' => $slotId,
                    'quality' => $qualityScore
                ]);
                return;
            }

            $empleado = Empleado::findOrFail($empleadoId);

            // Crear registro de huella
            $huella = Huella::create([
                'empleado_id' => $empleadoId,
                'numero_slot' => $slotId,
                'calidad' => $qualityScore,
                'estado' => 'Activa',
                'enrolado_por' => $adminId,
                'tipo_dedo' => $tipoDedo,
                'mano' => $mano,
            ]);

            // Actualizar estado del empleado
            $empleado->estado = 'Activo';
            $empleado->save();

            DB::commit();

            Log::channel('fingerprint')->info('Enrollment finalizado exitosamente desde comando', [
                'command_id' => $command->id,
                'huella_id' => $huella->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('fingerprint')->error('Error al finalizar enrollment desde comando', [
                'command_id' => $command->id,
                'error' => $e->getMessage()
            ]);
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
        // Obtener TODOS los slots ocupados en BD, sin importar el estado
        return Huella::pluck('numero_slot')->toArray();
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
     * Obtener slots ocupados directamente del sensor AS608
     * 
     * Llama a ESP32 GET /fingerprint/get-used-slots
     * Escaneo completo de 300 slots toma ~10-15 segundos
     * 
     * @return array Lista de slot IDs ocupados en el sensor
     * @throws \Exception Si falla la conexión con ESP32
     */
    public function getSensorUsedSlots(): array
    {
        try {
            // Timeout largo porque el escaneo toma 10-15 segundos
            $response = Http::timeout(20)
                ->get("{$this->esp32Url}/fingerprint/get-used-slots");

            if (!$response->successful()) {
                throw new \Exception('ESP32 no responde (HTTP ' . $response->status() . ')');
            }

            $data = $response->json();

            Log::channel('fingerprint')->info('Slots del sensor obtenidos', [
                'total_slots' => $data['total_slots'] ?? 0,
                'used_slots' => $data['used_slots'] ?? 0,
                'scan_time_ms' => $data['scan_time_ms'] ?? 0,
            ]);

            return $data['used_slot_ids'] ?? [];

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error al obtener slots del sensor', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Detectar huellas huérfanas: en sensor pero no en DB
     * 
     * Requiere que ESP32 exponga endpoint GET /fingerprint/get-used-slots
     * 
     * @return array Lista de slots huérfanos
     */
    public function findHuerfanas(): array
    {
        try {
            $slotsEnSensor = $this->getSensorUsedSlots();
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
            $slotsEnSensor = $this->getSensorUsedSlots();
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
