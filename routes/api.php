<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FingerprintController;
use App\Http\Controllers\Api\DeviceCommandController;


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

use App\Http\Controllers\Api\AlarmaController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\TelegramController;

/*
|--------------------------------------------------------------------------
| Alarm API Routes
|--------------------------------------------------------------------------
|
| Endpoints para gestión de alarmas y sensor PIR
|
*/

// Consulta rápida del estado de una alarma (optimizado para polling frecuente)
Route::get('/alarma/{id}/estado', [AlarmaController::class, 'getEstado']);

// Obtener información completa de una alarma específica
Route::get('/alarma/{id}', [AlarmaController::class, 'show']);

// Obtener todas las alarmas en estado "En Espera"
Route::get('/alarmas/en-espera', [AlarmaController::class, 'getAlarmasEnEspera']);

// Activar alarma manualmente (transición directa a Activa)
Route::get('/alarma/{id}/activar', [AlarmaController::class, 'activar']);

// Poner alarma en estado de espera (esperando señal del sensor PIR)
Route::get('/alarma/{id}/en-espera', [AlarmaController::class, 'ponerEnEspera']);

// Activar alarma cuando el sensor PIR detecta movimiento (En Espera -> Activa)
Route::post('/alarma/{id}/activar-por-movimiento', [AlarmaController::class, 'activarPorMovimiento']);

// Desactivar alarma (cualquier estado -> Apagada)
Route::get('/alarma/{id}/desactivar', [AlarmaController::class, 'desactivar']);

// Eventos
Route::get('/eventos', [EventoController::class, 'index']);
Route::post('/evento', [EventoController::class, 'store']);

//llamada de telegram

Route::get('/llamada/{alarma_id}/activar', [TelegramController::class, 'llamar']);

use App\Http\Controllers\Api\AsistenciaDiariaController;

Route::get('/registroasistencia/{huella_id}', [AsistenciaDiariaController::class, 'store']);

// Rutas de Comandos para ESP32 (Polling)
Route::get('/device/commands', [DeviceCommandController::class, 'index']);
Route::put('/device/commands/{id}', [DeviceCommandController::class, 'update']);

// Ruta de prueba para Telegram (TEMPORAL)
Route::get('/test-telegram/{username}', function ($username) {
    $url = "https://api.callmebot.com/start.php";
    $params = [
        'user'    => '@' . ltrim($username, '@'),
        'text'    => 'Esta es una llamada de prueba del sistema de alarma.',
        'lang'    => 'es-ES-Standard-A',
        'rpt'     => 2,
        'cc'      => 'yes',
        'timeout' => 30,
    ];

    $response = Illuminate\Support\Facades\Http::get($url, $params);

    return [
        'status' => $response->status(),
        'body' => $response->body(),
        'url_called' => $url,
        'params' => $params
    ];
});