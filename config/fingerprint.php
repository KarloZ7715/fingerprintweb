<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ESP32 Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para comunicación con el ESP32 que controla el sensor
    | de huellas AS608 y el sistema de alarmas PIR.
    |
    */

    'esp32_url' => env('ESP32_URL', 'http://fingerprintweb-esp32.local'),

    /*
    |--------------------------------------------------------------------------
    | Sensor Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración del sensor de huellas AS608
    |
    */

    'sensor' => [
        'capacity' => env('FINGERPRINT_SENSOR_CAPACITY', 300),
        'timeout' => env('FINGERPRINT_TIMEOUT', 10), // segundos
    ],

    /*
    |--------------------------------------------------------------------------
    | Enrollment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración del proceso de enrollment
    |
    */

    'enrollment' => [
        'polling_interval' => env('FINGERPRINT_POLLING_INTERVAL', 2), // segundos
        'max_retries' => env('FINGERPRINT_MAX_RETRIES', 3),
        'quality_threshold' => env('FINGERPRINT_QUALITY_THRESHOLD', 100), // 0-255
    ],

    /*
    |--------------------------------------------------------------------------
    | Reconciliation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para reconciliación de huellas huérfanas y fantasmas
    |
    */

    'reconciliation' => [
        'enabled' => env('FINGERPRINT_RECONCILIATION_ENABLED', true),
        'schedule' => env('FINGERPRINT_RECONCILIATION_SCHEDULE', 'daily'), // daily, hourly, weekly
    ],

];
