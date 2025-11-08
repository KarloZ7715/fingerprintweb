<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ESP32 Fingerprint Sensor Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para conectar con el ESP32 que maneja el sensor AS608.
    | Se usa mDNS para evitar problemas con IPs dinámicas en diferentes redes.
    |
    */
    'esp32' => [
        // URL base del ESP32 usando mDNS
        'fingerprint_url' => env('ESP32_FINGERPRINT_URL', 'http://fingerprintweb-esp32.local'),

        // Fallback IP manual (opcional, si mDNS falla)
        'fallback_ip' => env('ESP32_FALLBACK_IP', null),

        // Token de autenticación
        'api_token' => env('ESP32_API_TOKEN', null),

        // Timeouts en segundos
        'timeout' => env('ESP32_TIMEOUT', 30),
        'enroll_timeout' => env('ESP32_ENROLL_TIMEOUT', 60),

        // Configuración del sensor AS608
        'sensor' => [
            'total_slots' => 300, // Capacidad verificada del sensor
            'min_quality_score' => 80, // Calidad mínima aceptable (0-255)
        ],
    ],

];
