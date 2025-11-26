<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Alarma;
use App\Models\Evento;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificacionAlarma;
use Carbon\Carbon;

class AlarmaController extends Controller
{
    public function index()
    {
        return response()->json(Alarma::all());
    }

    public function show($id)
    {
        return response()->json(Alarma::findOrFail($id));
    }

    public function store(Request $request)
    {
        $alarma = Alarma::create($request->all());
        return response()->json($alarma, 201);
    }

    public function update(Request $request, $id)
    {
        $alarma = Alarma::findOrFail($id);
        $alarma->update($request->all());
        return response()->json($alarma);
    }

    /**
     * Activar alarma - Transición: cualquier estado -> "Activa"
     * Este endpoint se mantiene para compatibilidad con activaciones directas
     */
    public function activar($id)
    {
        $alarma = Alarma::findOrFail($id);
        $alarma->estado = Alarma::ESTADO_ACTIVA;
        $alarma->save();

        $fechaEnvio = Carbon::now('America/Bogota');

        $evento = Evento::create([
            'alarma_id' => $alarma->id,
            'fecha_evento' => $fechaEnvio,
            'Evento' => 'Activar',
            'Accion' => 'Alarma activada manualmente'
        ]);

        $contactos = \App\Models\ContactoEmergencia::where('sucursal_id', $alarma->sucursal_id)->get();

        foreach ($contactos as $contacto) {
            // Registro inicial, antes de enviar
            $envio = \App\Models\envio::create([
                'evento_id' => $evento->id,
                'contacto_id' => $contacto->id,
                'fecha_envio' => $fechaEnvio,
                'estado' => 'Pendiente',
                'forma' => 'Correo',
            ]);

            // Intentar enviar correo
            try {
                Mail::to($contacto->correo)
                    ->send(new NotificacionAlarma(
                        '¡Alarma activada!',
                        $alarma,
                        $evento
                    ));
                // Si NO hay excepción, marcar como Enviado
                $envio->estado = 'Enviado';
                $envio->save();
            } catch (\Exception $ex) {
                // Si ocurre error, marcar como Fallido
                $envio->estado = 'Fallido';
                $envio->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Alarma activada',
            'estado' => $alarma->estado
        ]);
    }

    /**
     * Poner alarma en estado de espera
     * Transición: "Apagada" -> "En Espera"
     * La alarma queda lista para activarse cuando el sensor PIR detecte movimiento
     */
    public function ponerEnEspera($id)
    {
        $alarma = Alarma::findOrFail($id);

        // Solo permitir transición desde Apagada
        if ($alarma->estado === Alarma::ESTADO_ACTIVA) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede poner en espera una alarma activa. Apáguela primero.'
            ], 400);
        }

        $alarma->estado = Alarma::ESTADO_EN_ESPERA;
        $alarma->save();

        $fechaEnvio = Carbon::now('America/Bogota');

        Evento::create([
            'alarma_id' => $alarma->id,
            'fecha_evento' => $fechaEnvio,
            'Evento' => 'Esperar',
            'Accion' => 'Alarma puesta en espera (esperando señal del sensor PIR)'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alarma puesta en estado de espera',
            'estado' => $alarma->estado
        ]);
    }

    public function desactivar($id)
    {
        $alarma = Alarma::findOrFail($id);
        $alarma->estado = Alarma::ESTADO_APAGADA;
        $alarma->save();

        $fechaEnvio = Carbon::now('America/Bogota');

        $evento = Evento::create([
            'alarma_id' => $alarma->id,
            'fecha_evento' => $fechaEnvio,
            'Evento' => 'Desactivar',
            'Accion' => 'Alarma desactivada por API'
        ]);

        // Opcionalmente, puedes hacer registros de envío aquí o no (según tu lógica)
        // No envía correos por especificación

        return response()->json(['message' => 'Alarma desactivada']);
    }

    /**
     * Obtener todas las alarmas en estado "En Espera"
     * Usado por ESP32 para verificar qué alarmas están esperando señal del sensor PIR
     */
    public function getAlarmasEnEspera()
    {
        $alarmas = Alarma::where('estado', Alarma::ESTADO_EN_ESPERA)->get();

        return response()->json([
            'success' => true,
            'count' => $alarmas->count(),
            'alarmas' => $alarmas->map(function ($alarma) {
                return [
                    'id' => $alarma->id,
                    'nombre' => $alarma->nombre,
                    'duracion' => $alarma->duracion,
                    'estado' => $alarma->estado,
                    'h_encendido' => $alarma->h_encendido,
                    'h_apagado' => $alarma->h_apagado,
                ];
            })
        ]);
    }

    /**
     * Activar alarma cuando el sensor PIR detecta movimiento
     * Transición: "En Espera" -> "Activa"
     */
    public function activarPorMovimiento($id)
    {
        $alarma = Alarma::findOrFail($id);

        // Verificar que la alarma esté en estado "En Espera"
        if ($alarma->estado !== Alarma::ESTADO_EN_ESPERA) {
            return response()->json([
                'success' => false,
                'message' => "La alarma debe estar en estado 'En Espera' para ser activada por movimiento",
                'estado_actual' => $alarma->estado
            ], 400);
        }

        // Cambiar estado a Activa
        $alarma->estado = Alarma::ESTADO_ACTIVA;
        $alarma->save();

        $fechaEnvio = Carbon::now('America/Bogota');

        // Registrar evento
        $evento = Evento::create([
            'alarma_id' => $alarma->id,
            'fecha_evento' => $fechaEnvio,
            'Evento' => 'Movimiento',
            'Accion' => 'Alarma activada: Se ha detectado movimiento.'
        ]);

        // Enviar notificaciones a contactos de emergencia
        $contactos = \App\Models\ContactoEmergencia::where('sucursal_id', $alarma->sucursal_id)->get();

        foreach ($contactos as $contacto) {
            $envio = \App\Models\envio::create([
                'evento_id' => $evento->id,
                'contacto_id' => $contacto->id,
                'fecha_envio' => $fechaEnvio,
                'estado' => 'Pendiente',
                'forma' => 'Correo',
            ]);

            try {
                Mail::to($contacto->correo)
                    ->send(new NotificacionAlarma(
                        '¡Alarma activada por movimiento!',
                        $alarma,
                        $evento
                    ));
                $envio->estado = 'Enviado';
                $envio->save();
            } catch (\Exception $ex) {
                $envio->estado = 'Fallido';
                $envio->save();
            }
        }

        // ----------------------------------------------------------------
        // NUEVO: Disparar llamadas de Telegram
        // ----------------------------------------------------------------
        try {
            $telegramController = new \App\Http\Controllers\Api\TelegramController();
            $telegramController->procesarLlamadas($alarma, $evento);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al disparar llamadas de Telegram desde AlarmaController: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Alarma activada por detección de movimiento',
            'alarma' => [
                'id' => $alarma->id,
                'nombre' => $alarma->nombre,
                'estado' => $alarma->estado,
                'duracion' => $alarma->duracion
            ]
        ]);
    }

    /**
     * Consulta rápida del estado de una alarma específica
     * Endpoint optimizado para polling frecuente del ESP32
     * Solo retorna el estado sin procesar eventos ni notificaciones
     */
    public function getEstado($id)
    {
        $alarma = Alarma::findOrFail($id);

        return response()->json([
            'success' => true,
            'alarma_id' => $alarma->id,
            'estado' => $alarma->estado
        ]);
    }
}
