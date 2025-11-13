<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/prueba', function () {
    return 'funciona la api!';
});

use App\Http\Controllers\Api\AlarmaController;
use App\Http\Controllers\Api\EventoController;

Route::get('/alarma/{id}/activar', [AlarmaController::class, 'activar']); //Enceder alarma
Route::get('/alarma/{id}/desactivar', [AlarmaController::class, 'desactivar']); //apagar alarma
Route::get('/eventos', [EventoController::class, 'index']);

Route::post('/evento', [EventoController::class, 'store']);

Route::get('/prueba', function () {
    return 'funciona la api!';
});

use App\Http\Controllers\Api\TelegramController;


Route::get('/telegram', [TelegramController::class, 'index']);
