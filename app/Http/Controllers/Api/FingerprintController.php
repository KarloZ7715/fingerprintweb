<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Huella;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador API para gestión de huellas dactilares
 * 
 * Endpoints consumidos por ESP32 con sensor AS608
 */
class FingerprintController extends Controller
{
    /**
     * Almacenar huella registrada por ESP32
     * 
     * POST /api/fingerprint/store
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'empleado_id' => 'required|exists:empleado,id',
            'slot_id' => 'required|integer|min:0|max:299|unique:huella,numero_slot',
            'template' => 'required|string',
            'quality_score' => 'required|integer|min:0|max:255',
        ]);

        if ($validator->fails()) {
            Log::channel('fingerprint')->warning('Validación fallida en store', [
                'errors' => $validator->errors(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Verificar que empleado esté en estado correcto
            $empleado = Empleado::findOrFail($request->empleado_id);

            if ($empleado->estado !== 'Pendiente_Huella') {
                return response()->json([
                    'success' => false,
                    'message' => 'El empleado no está pendiente de registro de huella',
                    'current_estado' => $empleado->estado,
                ], 400);
            }

            // Crear registro de huella
            $huella = Huella::create([
                'empleado_id' => $request->empleado_id,
                'numero_slot' => $request->slot_id,
                'template_huella' => base64_decode($request->template),
                'calidad' => $request->quality_score,
                'estado' => 'Activa',
                'enrolado_por' => $request->input('admin_id', null),
                'tipo_dedo' => $request->input('tipo_dedo', 'Indice'),
                'mano' => $request->input('mano', 'Derecha'),
            ]);

            // Actualizar estado del empleado a Activo
            $empleado->estado = 'Activo';
            $empleado->save();

            DB::commit();

            Log::channel('fingerprint')->info('Huella registrada exitosamente', [
                'empleado_id' => $empleado->id,
                'slot_id' => $huella->numero_slot,
                'quality' => $huella->calidad,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Huella registrada correctamente',
                'data' => [
                    'huella_id' => $huella->id,
                    'empleado_id' => $empleado->id,
                    'slot_id' => $huella->numero_slot,
                    'estado_empleado' => $empleado->estado,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('fingerprint')->error('Error al registrar huella', [
                'empleado_id' => $request->empleado_id,
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
            $usedSlots = Huella::where('estado', 'Activa')
                ->pluck('numero_slot')
                ->toArray();

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
            $huella = Huella::where('numero_slot', $slotId)->first();

            if (!$huella) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slot no encontrado en base de datos',
                ], 404);
            }

            DB::beginTransaction();

            $empleadoId = $huella->empleado_id;

            // Eliminar huella
            $huella->delete();

            // Revertir empleado a Pendiente_Huella
            $empleado = Empleado::find($empleadoId);
            if ($empleado && $empleado->estado === 'Activo') {
                $empleado->estado = 'Pendiente_Huella';
                $empleado->save();
            }

            DB::commit();

            Log::channel('fingerprint')->info('Slot eliminado (rollback)', [
                'slot_id' => $slotId,
                'empleado_id' => $empleadoId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slot eliminado correctamente',
                'slot_id' => $slotId,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

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
        $validator = Validator::make($request->all(), [
            'slot_id' => 'required|integer|min:0|max:299',
            'confidence' => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $huella = Huella::with(['empleado.horario', 'empleado.sucursal'])
                ->where('numero_slot', $request->slot_id)
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
                'slot_id' => $request->slot_id,
                'confidence' => $request->confidence,
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
                    'confidence' => $request->confidence,
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('fingerprint')->error('Error al identificar empleado', [
                'slot_id' => $request->slot_id,
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
            $usedSlots = Huella::where('estado', 'Activa')
                ->pluck('numero_slot')
                ->toArray();

            // Buscar primer slot libre (0-299)
            $availableSlot = null;
            for ($i = 0; $i < 300; $i++) {
                if (!in_array($i, $usedSlots)) {
                    $availableSlot = $i;
                    break;
                }
            }

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
