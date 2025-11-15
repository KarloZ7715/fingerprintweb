<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alarma;
use App\Models\Evento;
use App\Models\ContactoEmergencia;
use App\Models\envio;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    // Tiempo mínimo entre llamadas al mismo usuario según CallMeBot API
    private $tiempoMinimoEntreUsuario = 65;

    public function llamar($alarma_id)
    {
        $codigoEsperado = env('CODIGO_SEGURIDAD_API', '982323');
        $codigoRecibido = request()->input('seguridad', '');

        if ($codigoRecibido !== $codigoEsperado) {
            return response()->json([
                'status' => 'fallido',
                'mensaje' => 'Código de seguridad incorrecto'
            ], 401);
        }

        $alarma = Alarma::findOrFail($alarma_id);

        // Verificar horario
        $horaActual = Carbon::now()->format('H:i:s');
        $horaInicio = Carbon::createFromFormat('H:i:s', $alarma->h_encendido);
        $horaFin    = Carbon::createFromFormat('H:i:s', $alarma->h_apagado);

        $enHorario = false;
        if ($horaFin->greaterThan($horaInicio)) {
            $enHorario = $horaActual >= $horaInicio->format('H:i:s') && $horaActual <= $horaFin->format('H:i:s');
        } else {
            $enHorario = $horaActual >= $horaInicio->format('H:i:s') || $horaActual <= $horaFin->format('H:i:s');
        }

        if (!$enHorario) {
            return response()->json([
                'status' => 'fuera_horario',
                'mensaje' => 'La alarma no está dentro del horario de encendido. (h_encendido: ' . $horaInicio->format('H:i:s') . ', h_apagado: ' . $horaFin->format('H:i:s') . ', actual: ' . $horaActual . ')'
            ]);
        }

        // Crear evento
        $evento = Evento::create([
            'alarma_id'    => $alarma->id,
            'fecha_evento' => now(),
            'Evento'       => 'Llamada',
            'Accion'       => "Llamada de emergencia por activación de alarma",
        ]);

        $mensaje = "¡Alerta! Alarma '{$alarma->nombre}' activada en sucursal '{$alarma->sucursal->nombre}'.";

        // Obtener contactos y ordenarlos por prioridad
        $contactos = ContactoEmergencia::where('sucursal_id', $alarma->sucursal_id)
            ->orderByRaw("CASE WHEN prioridad = 'alta' THEN 1 WHEN prioridad = 'media' THEN 2 ELSE 3 END")
            ->get();

        $resultados = [
            'total' => $contactos->count(),
            'exitosos' => 0,
            'contestados' => 0,
            'no_contestados' => 0,
            'en_cola' => 0,
            'fallidos' => 0,
            'sin_cuenta' => 0,
            'ya_llamados' => 0,
            'detalles' => []
        ];

        // Preparar las llamadas que se realizarán
        $contactosParaLlamar = [];
        
        foreach ($contactos as $contacto) {
            $usuarioTele = trim($contacto->usario_tele ?? '');
            
            if (!$usuarioTele) {
                // REGISTRAR SOLO UNA VEZ - Sin cuenta
                $this->registrarEnvio($evento->id, $contacto->id, 'Sin cuenta', 'Sin cuenta de Telegram configurada', null);
                $resultados['sin_cuenta']++;
                $resultados['detalles'][] = [
                    'contacto' => $contacto->nombre,
                    'estado' => 'Sin cuenta',
                    'prioridad' => $contacto->prioridad
                ];
                continue;
            }

            // Verificar si ya se llamó a este usuario recientemente
            $ultimaLlamada = $this->obtenerUltimaLlamada($usuarioTele);
            if ($ultimaLlamada) {
                $segundosTranscurridos = now()->diffInSeconds($ultimaLlamada);
                $segundosRestantes = $this->tiempoMinimoEntreUsuario - $segundosTranscurridos;
                
                if ($segundosRestantes > 0) {
                    $mensajeOmision = "Usuario ya fue llamado exitosamente hace {$segundosTranscurridos} segundos (última llamada: {$ultimaLlamada->format('H:i:s')}). Llamada omitida para evitar duplicados.";
                    
                    Log::info($mensajeOmision);
                    
                    // REGISTRAR SOLO UNA VEZ - Ya llamado
                    $this->registrarEnvio($evento->id, $contacto->id, 'Enviado', $mensajeOmision, 'ya_llamado');
                    $resultados['ya_llamados']++;
                    $resultados['exitosos']++;
                    $resultados['detalles'][] = [
                        'contacto' => $contacto->nombre,
                        'usuario' => '@' . $usuarioTele,
                        'estado' => 'Enviado',
                        'prioridad' => $contacto->prioridad,
                        'mensaje' => $mensajeOmision,
                        'ya_llamado' => true
                    ];
                    continue;
                }
            }

            // Agregar a la lista de contactos para llamar simultáneamente
            $contactosParaLlamar[] = [
                'contacto' => $contacto,
                'usuario_tele' => $usuarioTele
            ];
        }

        // Realizar TODAS las llamadas en PARALELO usando Http::pool()
        if (!empty($contactosParaLlamar)) {
            Log::info("Iniciando " . count($contactosParaLlamar) . " llamadas simultáneas...");
            
            $respuestas = $this->realizarLlamadasSimultaneas($contactosParaLlamar, $mensaje);
            
            // Procesar las respuestas - REGISTRAR SOLO UNA VEZ AQUÍ
            foreach ($respuestas as $index => $respuestaData) {
                $contacto = $contactosParaLlamar[$index]['contacto'];
                $usuarioTele = $contactosParaLlamar[$index]['usuario_tele'];
                $respuesta = $respuestaData['respuesta'];
                $success = $respuestaData['success'];
                
                // Analizar la respuesta
                if ($success) {
                    $analisis = $this->analizarRespuestaCallMeBot($respuesta);
                } else {
                    // Si hubo error de conexión o timeout
                    $analisis = [
                        'exitoso' => false,
                        'contestado' => false,
                        'en_cola' => false,
                        'mensaje_detallado' => '❌ Error de conexión o timeout'
                    ];
                }
                
                // Determinar estado
                $estado = $analisis['exitoso'] ? 'Enviado' : 'Fallido';
                
                // Verificar si es error de límite de 65 segundos (significa que fue exitoso antes)
                if (!$analisis['exitoso'] && 
                    (str_contains($respuesta, '65 segundos') || str_contains($respuesta, 'No se permiten dos llamadas'))) {
                    $estado = 'Enviado';
                    $analisis['exitoso'] = true;
                    $analisis['mensaje_detallado'] = '✅ Usuario ya fue llamado exitosamente (rechazado por límite de 65s)';
                    $this->guardarUltimaLlamada($usuarioTele);
                }
                
                // Registrar en base de datos - SOLO UNA VEZ
                $detalle = $analisis['exitoso'] 
                    ? "Llamada exitosa. {$analisis['mensaje_detallado']}" 
                    : "Llamada fallida. {$analisis['mensaje_detallado']}";
                
                $this->registrarEnvio(
                    $evento->id, 
                    $contacto->id, 
                    $estado, 
                    $detalle,
                    $respuesta
                );
                
                // Actualizar contadores
                if ($estado == 'Enviado') {
                    $resultados['exitosos']++;
                    
                    if ($analisis['contestado'] ?? false) {
                        $resultados['contestados']++;
                    } elseif ($analisis['en_cola'] ?? false) {
                        $resultados['en_cola']++;
                    } else {
                        $resultados['no_contestados']++;
                    }
                    
                    // Guardar en cache la última llamada exitosa
                    $this->guardarUltimaLlamada($usuarioTele);
                } else {
                    $resultados['fallidos']++;
                }
                
                $resultados['detalles'][] = [
                    'contacto' => $contacto->nombre,
                    'usuario' => '@' . $usuarioTele,
                    'estado' => $estado,
                    'prioridad' => $contacto->prioridad,
                    'contestado' => $analisis['contestado'] ?? false,
                    'en_cola' => $analisis['en_cola'] ?? false,
                    'mensaje' => $detalle,
                    'respuesta_api' => $respuesta
                ];
            }
        }

        // Determinar status final
        $statusFinal = 'ok';
        if ($resultados['exitosos'] == 0 && $resultados['ya_llamados'] == 0) {
            $statusFinal = 'fallido';
        } elseif ($resultados['fallidos'] > 0 || $resultados['sin_cuenta'] > 0) {
            $statusFinal = 'parcial';
        }

        return response()->json([
            'status' => $statusFinal,
            'mensaje' => $this->generarMensajeEstado($resultados),
            'resultados' => $resultados
        ]);
    }

    /**
     * Realiza múltiples llamadas HTTP simultáneas usando Http::pool()
     */
    private function realizarLlamadasSimultaneas($contactosParaLlamar, $mensaje)
    {
        $url = "https://api.callmebot.com/start.php";
        
        // Crear un array de requests para el pool
        $responses = Http::pool(function ($pool) use ($contactosParaLlamar, $mensaje, $url) {
            $requests = [];
            
            foreach ($contactosParaLlamar as $data) {
                $usuarioTele = $data['usuario_tele'];
                
                $params = [
                    'user'    => '@' . ltrim($usuarioTele, '@'),
                    'text'    => $mensaje,
                    'lang'    => env('CALLMEBOT_LANG', 'es-ES-Standard-A'),
                    'rpt'     => env('CALLMEBOT_REPETICIONES', 2),
                    'cc'      => 'yes',
                    'timeout' => 30,
                ];
                
                Log::info("Preparando llamada simultánea para @{$usuarioTele}");
                
                // Agregar request al pool con timeout de 40 segundos
                $requests[] = $pool->timeout(40)->get($url, $params);
            }
            
            return $requests;
        });
        
        // Procesar respuestas
        $resultados = [];
        foreach ($responses as $index => $response) {
            $usuarioTele = $contactosParaLlamar[$index]['usuario_tele'];
            
            if ($response->successful()) {
                $body = $response->body();
                Log::info("Respuesta de CallMeBot para @{$usuarioTele}", ['body' => $body]);
                
                if (stripos($body, 'ERROR:') !== false) {
                    $resultados[] = [
                        'success' => false,
                        'respuesta' => "ERROR: " . $body
                    ];
                } else {
                    $resultados[] = [
                        'success' => true,
                        'respuesta' => "OK: " . $body
                    ];
                }
            } else {
                $statusCode = $response->status();
                $body = $response->body();
                
                Log::error("Error en llamada para @{$usuarioTele}", [
                    'status' => $statusCode,
                    'body' => $body
                ]);
                
                $resultados[] = [
                    'success' => false,
                    'respuesta' => "ERROR: HTTP {$statusCode} - {$body}"
                ];
            }
        }
        
        return $resultados;
    }

    /**
     * Genera mensaje descriptivo del estado de las llamadas
     */
    private function generarMensajeEstado($resultados)
    {
        if ($resultados['exitosos'] == $resultados['total']) {
            $msg = 'Todas las llamadas se realizaron correctamente';
            if ($resultados['contestados'] > 0) {
                $msg .= " ({$resultados['contestados']} contestada(s))";
            }
            return $msg;
        }
        
        $partes = [];
        
        if ($resultados['exitosos'] > 0) {
            $msg = "{$resultados['exitosos']} exitosa(s)";
            $subPartes = [];
            if ($resultados['contestados'] > 0) {
                $subPartes[] = "{$resultados['contestados']} contestada(s)";
            }
            if ($resultados['en_cola'] > 0) {
                $subPartes[] = "{$resultados['en_cola']} en cola";
            }
            if ($resultados['no_contestados'] > 0) {
                $subPartes[] = "{$resultados['no_contestados']} no contestada(s)";
            }
            if ($resultados['ya_llamados'] > 0) {
                $subPartes[] = "{$resultados['ya_llamados']} ya llamado(s)";
            }
            if (!empty($subPartes)) {
                $msg .= " (" . implode(', ', $subPartes) . ")";
            }
            $partes[] = $msg;
        }
        if ($resultados['fallidos'] > 0) {
            $partes[] = "{$resultados['fallidos']} fallida(s)";
        }
        if ($resultados['sin_cuenta'] > 0) {
            $partes[] = "{$resultados['sin_cuenta']} sin cuenta";
        }
        
        return 'Llamadas: ' . implode(', ', $partes);
    }

    /**
     * Obtiene la última vez que se llamó a un usuario
     */
    private function obtenerUltimaLlamada($usuario_tele)
    {
        $cacheKey = 'callmebot_last_call_' . strtolower($usuario_tele);
        return Cache::get($cacheKey);
    }

    /**
     * Guarda el timestamp de la última llamada a un usuario
     */
    private function guardarUltimaLlamada($usuario_tele)
    {
        $cacheKey = 'callmebot_last_call_' . strtolower($usuario_tele);
        Cache::put($cacheKey, now(), 90);
        Log::info("Guardada última llamada para @{$usuario_tele} a las " . now()->format('H:i:s'));
    }

    /**
     * Analiza la respuesta de CallMeBot para determinar el estado de la llamada
     */
    private function analizarRespuestaCallMeBot($respuesta)
    {
        $respuestaLower = strtolower($respuesta);
        
        $resultado = [
            'exitoso' => false,
            'contestado' => false,
            'en_cola' => false,
            'mensaje_detallado' => ''
        ];
        
        // Detectar si fue exitoso
        if (str_contains($respuestaLower, 'ok:') || str_contains($respuestaLower, 'success')) {
            $resultado['exitoso'] = true;
        }
        
        // Detectar si el usuario contestó la llamada
        if (str_contains($respuestaLower, 'llamada contestada')) {
            $resultado['contestado'] = true;
            $resultado['mensaje_detallado'] = '✅ Llamada contestada y finalizada por el usuario';
        } 
        // NUEVO: Detectar si está en cola (NO ES ERROR)
        elseif (str_contains($respuestaLower, 'line is busy') || 
                str_contains($respuestaLower, 'call queued') ||
                str_contains($respuestaLower, 'call has been queued')) {
            $resultado['exitoso'] = true; // ¡ES EXITOSO!
            $resultado['contestado'] = false;
            $resultado['en_cola'] = true;
            $resultado['mensaje_detallado'] = '📞 Llamada en cola (se realizará en 10-20 segundos)';
        }
        elseif (str_contains($respuestaLower, 'no contestada') || 
                str_contains($respuestaLower, 'not answered') ||
                str_contains($respuestaLower, 'no answer')) {
            $resultado['contestado'] = false;
            $resultado['mensaje_detallado'] = '📵 Llamada realizada pero no contestada';
        }
        elseif (str_contains($respuestaLower, 'busy') || str_contains($respuestaLower, 'ocupado')) {
            $resultado['contestado'] = false;
            $resultado['mensaje_detallado'] = '📞 Usuario ocupado';
        }
        elseif (str_contains($respuestaLower, 'voicemail') || str_contains($respuestaLower, 'buzón')) {
            $resultado['contestado'] = false;
            $resultado['mensaje_detallado'] = '📬 Buzón de voz';
        }
        elseif ($resultado['exitoso']) {
            $resultado['mensaje_detallado'] = '✓ Llamada enviada correctamente';
        }
        
        // Palabras que indican fallo REAL
        $indicadoresFallo = [
            'error: usuario',
            'no authorized',
            'not authorized',
            'oops',
            'rejected',
            'failed',
            'exception',
            'invalid',
            'not found',
            'user not found',
            'not registered',
            'no registrado'
        ];
        
        foreach ($indicadoresFallo as $indicador) {
            if (str_contains($respuestaLower, $indicador)) {
                $resultado['exitoso'] = false;
                $resultado['contestado'] = false;
                $resultado['en_cola'] = false;
                $resultado['mensaje_detallado'] = '❌ Error: ' . $indicador;
                break;
            }
        }
        
        return $resultado;
    }

    /**
     * Registra el envío en la base de datos
     */
    private function registrarEnvio($evento_id, $contacto_id, $estado, $detalle, $respuesta_api = null)
    {
        $detalleCompleto = $detalle;
        if ($respuesta_api && $respuesta_api !== 'ya_llamado') {
            $detalleCompleto .= "\n\nRespuesta API: " . $respuesta_api;
        }
        
        envio::create([
            'evento_id'   => $evento_id,
            'contacto_id' => $contacto_id,
            'fecha_envio' => now(),
            'estado'      => $estado,
            'forma'       => 'Telegram-Llamada',
            'detalle'     => $detalleCompleto,
        ]);
    }
}