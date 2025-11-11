<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Servicio para descubrir automáticamente la IP del ESP32 en la red local
 * 
 * Estrategia:
 * 1. Intenta mDNS (.local) primero
 * 2. Si falla, escanea rango de IPs comunes en redes domésticas
 * 3. Cachea la IP descubierta por 1 hora
 */
class ESP32DiscoveryService
{
    /**
     * Timeout en segundos para cada request HTTP
     */
    private const REQUEST_TIMEOUT = 2;

    /**
     * TTL del cache en segundos (1 hora)
     */
    private const CACHE_TTL = 3600;

    /**
     * Prefijos de redes comunes a escanear
     */
    private const NETWORK_PREFIXES = [
        '192.168.1.',
        '192.168.0.',
        '192.168.100.',
        '10.0.0.',
    ];

    /**
     * Rango de hosts a escanear en cada subred
     */
    private const HOST_RANGE_START = 1;
    private const HOST_RANGE_END = 254;

    /**
     * Obtiene la URL del ESP32, con discovery automático si es necesario
     * 
     * @return string|null URL completa del ESP32 o null si no se encuentra
     */
    public function getESP32Url(): ?string
    {
        // Intento 1: Verificar cache
        $cachedIP = Cache::get('esp32_ip');
        if ($cachedIP && $this->verifyESP32($cachedIP)) {
            logger()->debug('[ESP32 Discovery] IP encontrada en cache', ['ip' => $cachedIP]);
            return "http://{$cachedIP}";
        }

        // Intento 2: Probar mDNS (.local)
        $mdnsHost = 'fingerprintweb-esp32.local';
        if ($this->verifyESP32($mdnsHost)) {
            logger()->info('[ESP32 Discovery] ESP32 accesible vía mDNS', ['host' => $mdnsHost]);

            // Intentar resolver la IP para cachear
            $resolvedIP = gethostbyname($mdnsHost);
            if ($resolvedIP !== $mdnsHost) {
                Cache::put('esp32_ip', $resolvedIP, self::CACHE_TTL);
            }

            return "http://{$mdnsHost}";
        }

        // Intento 3: Discovery por escaneo de red (solo en desarrollo/local)
        if (config('app.env') === 'local') {
            logger()->info('[ESP32 Discovery] Iniciando escaneo de red...');
            $discoveredIP = $this->scanNetwork();

            if ($discoveredIP) {
                logger()->info('[ESP32 Discovery] ✓ ESP32 encontrado', ['ip' => $discoveredIP]);
                Cache::put('esp32_ip', $discoveredIP, self::CACHE_TTL);
                return "http://{$discoveredIP}";
            }
        }

        logger()->error('[ESP32 Discovery] ✗ No se pudo encontrar el ESP32', [
            'mdns_tried' => $mdnsHost,
            'cache_expired' => $cachedIP === null,
        ]);

        return null;
    }

    /**
     * Verifica si un host responde correctamente como ESP32
     * 
     * @param string $host IP o hostname a verificar
     * @return bool true si responde correctamente
     */
    private function verifyESP32(string $host): bool
    {
        try {
            $response = Http::timeout(self::REQUEST_TIMEOUT)
                ->get("http://{$host}/ip");

            if ($response->successful() && isset($response->json()['ip'])) {
                return true;
            }
        } catch (\Exception $e) {
            logger()->debug('[ESP32 Discovery] Verificación fallida', [
                'host' => $host,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * Escanea rangos de red comunes buscando el ESP32
     * 
     * ADVERTENCIA: Este proceso es lento (varios segundos)
     * Solo se ejecuta si no hay IP en cache y mDNS falla
     * 
     * @return string|null IP encontrada o null
     */
    private function scanNetwork(): ?string
    {
        foreach (self::NETWORK_PREFIXES as $prefix) {
            logger()->debug('[ESP32 Discovery] Escaneando subred', ['prefix' => $prefix]);

            // Escaneo paralelo de IPs comunes primero (router suele asignar IPs bajas)
            $commonHosts = [100, 101, 102, 103, 104, 105, 50, 51, 52, 53, 10, 11, 12, 13];

            foreach ($commonHosts as $host) {
                $ip = $prefix . $host;

                if ($this->verifyESP32($ip)) {
                    return $ip;
                }
            }
        }

        logger()->warning('[ESP32 Discovery] No se encontró ESP32 en escaneo rápido');
        return null;
    }

    /**
     * Limpia el cache de IP (útil para forzar re-discovery)
     * 
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('esp32_ip');
        logger()->info('[ESP32 Discovery] Cache limpiado');
    }

    /**
     * Obtiene información de debugging sobre el discovery
     * 
     * @return array Estado actual del discovery
     */
    public function getDebugInfo(): array
    {
        $cachedIP = Cache::get('esp32_ip');

        return [
            'cached_ip' => $cachedIP,
            'cache_valid' => $cachedIP && $this->verifyESP32($cachedIP),
            'mdns_accessible' => $this->verifyESP32('fingerprintweb-esp32.local'),
            'env_url' => config('fingerprint.esp32_url'),
        ];
    }
}
