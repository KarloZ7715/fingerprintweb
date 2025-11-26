<?php

namespace App\Console\Commands;

use App\Services\ESP32DiscoveryService;
use Illuminate\Console\Command;

class TestESP32Discovery extends Command
{
    protected $signature = 'esp32:discover 
                            {--clear : Limpiar cache antes de descubrir}
                            {--debug : Mostrar información de debugging}';

    protected $description = 'Descubre automáticamente la IP del ESP32 en la red local';

    public function handle(ESP32DiscoveryService $discoveryService): int
    {
        $this->info('Iniciando discovery del ESP32...');
        $this->newLine();

        // Limpiar cache si se solicita
        if ($this->option('clear')) {
            $discoveryService->clearCache();
            $this->warn('Cache limpiado. Forzando re-discovery...');
            $this->newLine();
        }

        // Mostrar info de debugging si se solicita
        if ($this->option('debug')) {
            $debugInfo = $discoveryService->getDebugInfo();
            $this->line('<fg=gray>Debug Info:</>');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Cached IP', $debugInfo['cached_ip'] ?? 'null'],
                    ['Cache válido', $debugInfo['cache_valid'] ? 'Sí' : 'No'],
                    ['mDNS accesible', $debugInfo['mdns_accessible'] ? 'Sí' : 'No'],
                    ['URL en .env', $debugInfo['env_url']],
                ]
            );
            $this->newLine();
        }

        // Intentar descubrir
        $startTime = microtime(true);
        $url = $discoveryService->getESP32Url();
        $elapsed = round((microtime(true) - $startTime) * 1000, 2);

        if ($url) {
            $this->info("✓ ESP32 encontrado en {$elapsed}ms:");
            $this->line("  <fg=green>{$url}</>");
            $this->newLine();

            // Intentar obtener info adicional
            $this->line('Verificando conectividad...');
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(3)->get("{$url}/ip");

                if ($response->successful()) {
                    $data = $response->json();
                    $this->info("✓ Respuesta del ESP32:");
                    $this->line("  IP reportada: {$data['ip']}");
                    $this->newLine();

                    // Obtener más info
                    $infoResponse = \Illuminate\Support\Facades\Http::timeout(3)->get("{$url}/info");
                    if ($infoResponse->successful()) {
                        $info = $infoResponse->json();
                        $this->line('<fg=cyan>Información del dispositivo:</>');
                        $this->table(
                            ['Campo', 'Valor'],
                            [
                                ['WiFi SSID', $info['wifi']['ssid'] ?? 'N/A'],
                                ['IP', $info['wifi']['ip'] ?? 'N/A'],
                                ['RSSI', ($info['wifi']['rssi'] ?? 'N/A') . ' dBm'],
                                ['Slots totales', $info['fingerprint']['total_slots'] ?? 'N/A'],
                                ['Slots usados', $info['fingerprint']['used_slots'] ?? 'N/A'],
                                ['Slots disponibles', $info['fingerprint']['available_slots'] ?? 'N/A'],
                                ['Uptime', round(($info['system']['uptime_seconds'] ?? 0) / 60, 2) . ' min'],
                            ]
                        );
                    }

                    return Command::SUCCESS;
                }
            } catch (\Exception $e) {
                $this->error('✗ Error al verificar conectividad: ' . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            $this->error("✗ No se pudo encontrar el ESP32 después de {$elapsed}ms");
            $this->newLine();
            $this->line('<fg=yellow>Posibles soluciones:</>');
            $this->line('  1. Verifica que el ESP32 esté encendido y conectado a la red');
            $this->line('  2. Confirma que estás en la misma red que el ESP32');
            $this->line('  3. Si usas Docker, verifica la configuración de red');
            $this->line('  4. Intenta acceder manualmente a http://fingerprintweb-esp32.local');
            $this->newLine();

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
