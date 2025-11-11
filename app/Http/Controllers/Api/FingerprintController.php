<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFingerprintRequest;
use App\Models\Empleado;
use App\Models\Huella;
use App\Services\FingerprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador API para gestión de huellas dactilares
 * 
 * Endpoints consumidos por ESP32 con sensor AS608
 * Delega lógica de negocio a FingerprintService
 */
class FingerprintController extends Controller
{
    /**
     * Constructor con inyección de dependencias
     */
    public function __construct(
        private FingerprintService $fingerprintService
    ) {
    }
    /**
     * Almacenar huella registrada por ESP32
     * 
     * POST /api/fingerprint/store
     * 
     * @param StoreFingerprintRequest $request Validación automática
     * @return JsonResponse
     */
    public function store(StoreFingerprintRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Delegar lógica de negocio al service
            $result = $this->fingerprintService->enrollFingerprint(
                $validated['empleado_id'],
                $validated['slot_id'],
                $validated['quality_score'],
                $validated['admin_id'] ?? null,
                $validated['tipo_dedo'] ?? 'Indice',
                $validated['mano'] ?? 'Derecha'
            );

            Log::channel('fingerprint')->info('Huella registrada exitosamente', [
                'empleado_id' => $result['empleado_id'],
                'slot_id' => $result['slot_id'],
                'quality' => $validated['quality_score'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Huella registrada correctamente',
                'data' => $result,
            ], 201);

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error al registrar huella', [
                'empleado_id' => $request->input('empleado_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar huella',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener slots ocupados en la base de datos
     * 
     * GET /api/fingerprint/slots
     * 
     * @return JsonResponse
     */
    public function getUsedSlots(): JsonResponse
    {
        try {
            $usedSlots = $this->fingerprintService->getUsedSlots();

            return response()->json([
                'success' => true,
                'total_slots' => 300,
                'used_slots' => count($usedSlots),
                'available_slots' => 300 - count($usedSlots),
                'used_slot_ids' => $usedSlots,
            ]);

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error al obtener slots', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener slots',
            ], 500);
        }
    }

    /**
     * Eliminar huella de un slot (rollback)
     * 
     * DELETE /api/fingerprint/slot/{id}
     * 
     * @param int $slotId
     * @return JsonResponse
     */
    public function deleteSlot(int $slotId): JsonResponse
    {
        try {
            // Verificar que el slot existe en DB
            if (!$this->fingerprintService->isSlotOccupied($slotId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slot no encontrado en base de datos',
                ], 404);
            }

            // Delegar rollback al service
            $success = $this->fingerprintService->rollbackEnrollment($slotId);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar slot del sensor ESP32',
                ], 500);
            }

            Log::channel('fingerprint')->info('Slot eliminado (rollback)', [
                'slot_id' => $slotId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slot eliminado correctamente',
                'slot_id' => $slotId,
            ]);

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error al eliminar slot', [
                'slot_id' => $slotId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar slot',
            ], 500);
        }
    }

    /**
     * Identificar empleado por huella
     * 
     * POST /api/fingerprint/identify
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function identify(Request $request): JsonResponse
    {
        // Validación inline
        $validated = $request->validate([
            'slot_id' => 'required|integer|min:0|max:299',
            'confidence' => 'required|integer|min:0|max:100',
        ]);

        try {
            $huella = Huella::with(['empleado.horario', 'empleado.sucursal'])
                ->where('numero_slot', $validated['slot_id'])
                ->where('estado', 'Activa')
                ->first();

            if (!$huella) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huella no encontrada',
                ], 404);
            }

            $empleado = $huella->empleado;

            Log::channel('fingerprint')->info('Empleado identificado', [
                'empleado_id' => $empleado->id,
                'slot_id' => $validated['slot_id'],
                'confidence' => $validated['confidence'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Empleado identificado',
                'data' => [
                    'empleado_id' => $empleado->id,
                    'cedula' => $empleado->cedula,
                    'nombre_completo' => $empleado->nombre_completo,
                    'estado' => $empleado->estado,
                    'sucursal' => $empleado->sucursal ? [
                        'id' => $empleado->sucursal->id,
                        'nombre' => $empleado->sucursal->nombre,
                    ] : null,
                    'horario' => $empleado->horario ? [
                        'id' => $empleado->horario->id,
                        'nombre' => $empleado->horario->nombre ?? null,
                    ] : null,
                    'confidence' => $validated['confidence'],
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error al identificar empleado', [
                'slot_id' => $validated['slot_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al identificar empleado',
            ], 500);
        }
    }

    /**
     * Obtener primer slot disponible
     * 
     * GET /api/fingerprint/available-slot
     * 
     * @return JsonResponse
     */
    public function getAvailableSlot(): JsonResponse
    {
        try {
            $availableSlot = $this->fingerprintService->getAvailableSlot();
            $usedSlots = $this->fingerprintService->getUsedSlots();

            if ($availableSlot === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sensor lleno: no hay slots disponibles',
                    'total_slots' => 300,
                    'used_slots' => count($usedSlots),
                ], 507); // Insufficient Storage
            }

            return response()->json([
                'success' => true,
                'available_slot' => $availableSlot,
                'total_slots' => 300,
                'used_slots' => count($usedSlots),
            ]);

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error al buscar slot disponible', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar slot disponible',
            ], 500);
        }
    }
}
