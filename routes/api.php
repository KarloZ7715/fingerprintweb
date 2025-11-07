<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlarmaController;
use App\Http\Controllers\Api\EventoController;

Route::get('/alarma/{id}/estado', [AlarmaController::class, 'show']);
Route::get('/alarma/{id}/activar', [AlarmaController::class, 'activar']);
Route::get('/alarma/{id}/desactivar', [AlarmaController::class, 'desactivar']);
Route::get('/eventos', [EventoController::class, 'index']);

Route::post('/evento', [EventoController::class, 'store']);

Route::get('/prueba', function () {
    return 'funciona la api!';
});