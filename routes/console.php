<?php

use App\Jobs\DetectPendingEmployees;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// Scheduled Tasks - Sistema de Huellas
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

// 1. Reconciliación automática: 2:00 AM todos los días
// Limpia inconsistencias entre sensor AS608 y base de datos
Schedule::command('fingerprint:reconcile --force')
    ->dailyAt('02:00')
    ->timezone('America/Bogota')
    ->withoutOverlapping(); // No ejecutar si hay uno corriendo

// 2. Detección de empleados pendientes: 8:00 AM todos los días
// Notifica a administradores sobre empleados >24h sin huella
Schedule::job(new DetectPendingEmployees())
    ->dailyAt('08:00')
    ->timezone('America/Bogota')
    ->withoutOverlapping();

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// Scheduled Tasks - Sistema de Asistencias
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

// 3. Detectar ausencias: 6:00 PM todos los días
// Marca como ausentes a empleados que no registraron entrada
Schedule::job(new \App\Jobs\DetectarAusenciasDiarias())
    ->dailyAt('18:00')
    ->timezone('America/Bogota')
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Job DetectarAusenciasDiarias ejecutado exitosamente');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Job DetectarAusenciasDiarias falló');
    });
