<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alarma;
use App\Models\Evento;
use App\Models\ContactoEmergencia;
use App\Models\envio;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class TelegramController extends Controller
{
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
        $horaActual = Carbon::now()->format('H:i:s');      // Por ejemplo 13:07:24
        $horaInicio = Carbon::createFromFormat('H:i:s', $alarma->h_encendido);
        $horaFin    = Carbon::createFromFormat('H:i:s', $alarma->h_apagado);

        // Ejemplo: manejo de horarios que cruzan medianoche
        $enHorario = false;
        if ($horaFin->greaterThan($horaInicio)) {
            // Horario normal (ej. 07:00 - 21:00)
            $enHorario = $horaActual >= $horaInicio->format('H:i:s') && $horaActual <= $horaFin->format('H:i:s');
        } else {
            // Horario que cruza medianoche (ej. 20:00 - 06:00)
            $enHorario = $horaActual >= $horaInicio->format('H:i:s') || $horaActual <= $horaFin->format('H:i:s');
        }

        if (!$enHorario) {
            return response()->json([
                'status' => 'fuera_horario',
                'mensaje' => 'La alarma no está dentro del horario de encendido. (h_encendido: ' . $horaInicio->format('H:i:s') . ', h_apagado: ' . $horaFin->format('H:i:s') . ', actual: ' . $horaActual . ')'
            ]);
        }

        // Si está en horario, procede a llamada como antes
        $evento = Evento::create([
            'alarma_id'    => $alarma->id,
            'fecha_evento' => now(),
            'Evento'       => 'Llamada',
            'Accion'       => "Llamada de emergencia por activación de alarma",
        ]);

        $mensaje = "¡Alerta! Alarma '{$alarma->nombre}' activada en sucursal '{$alarma->sucursal->nombre}'.";

        $contactos = ContactoEmergencia::where('sucursal_id', $alarma->sucursal_id)->get();

        $todoOk = true;

        foreach ($contactos as $contacto) {
            $usuarioTele = $contacto->usario_tele;
            $estadoEnvio = 'Enviado';
            $logTxt = '';

            if (!$usuarioTele || trim($usuarioTele) == '') {
                $estadoEnvio = 'Sin cuenta';
                $logTxt = "sin cuenta de Telegram";
                $todoOk = false;
            } else {
                $logTxt = $this->llamarBotTelegram($usuarioTele, $mensaje);
                if (
                    str_contains($logTxt, 'ERROR') ||
                    str_contains($logTxt, 'no authorized') ||
                    str_contains($logTxt, 'Oops') ||
                    str_contains($logTxt, 'Rejected')
                ) {
                    $estadoEnvio = 'Fallido';
                    $todoOk = false;
                }
            }

            envio::create([
                'evento_id'   => $evento->id,
                'contacto_id' => $contacto->id,
                'fecha_envio' => now(),
                'estado'      => $estadoEnvio,
                'forma'       => 'Telegram-Llamada',
                'detalle'     => $logTxt,
            ]);

            if ($contacto->prioridad == 'alta') {
                sleep(5);
            }
        }

        return response()->json([
            'status' => $todoOk ? 'ok' : 'fallido'
        ]);
    }

    private function llamarBotTelegram($usuario_tele, $mensaje)
    {
        $url = "https://api.callmebot.com/start.php";
        $params = [
            'user'    => '@' . $usuario_tele,
            'text'    => $mensaje,
            'lang'    => 'es-ES-Standard-A',
            'rpt'     => 1,
            'cc'      => 'yes',
            'timeout' => 30,
        ];
        try {
            $response = Http::get($url, $params);
            $body = $response->body();
            if (stripos($body, 'ERROR:') !== false) {
                return "ERROR: " . $body;
            }
            return "OK: " . $body;
        } catch (\Exception $ex) {
            return "EXCEPTION: " . $ex->getMessage();
        }
    }
}