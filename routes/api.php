<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FingerprintController;

/*
|--------------------------------------------------------------------------
| Fingerprint API Routes
|--------------------------------------------------------------------------
|
| Endpoints consumidos por ESP32 con sensor AS608
|
*/

// Almacenar huella registrada por ESP32
Route::post('/fingerprint/store', [FingerprintController::class, 'store']);

// Obtener slots ocupados
Route::get('/fingerprint/slots', [FingerprintController::class, 'getUsedSlots']);

// Eliminar slot (rollback)
Route::delete('/fingerprint/slot/{id}', [FingerprintController::class, 'deleteSlot']);

// Identificar empleado por huella
Route::post('/fingerprint/identify', [FingerprintController::class, 'identify']);

// Obtener primer slot disponible
Route::get('/fingerprint/available-slot', [FingerprintController::class, 'getAvailableSlot']);

// Ruta de prueba
Route::get('/prueba', function () {
    return response()->json([
        'message' => 'API Fingerprint funcionando correctamente',
        'timestamp' => now()->toIso8601String(),
    ]);
});
