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

    public function activar($id)
    {
        $alarma = Alarma::findOrFail($id);
        $alarma->estado = 'Activa';
        $alarma->save();

        $fechaEnvio = Carbon::now('America/Bogota');

        $evento = Evento::create([
            'alarma_id' => $alarma->id,
            'fecha_evento' => $fechaEnvio,
            'Evento' => 'Activar',
            'Accion' => 'Alarma activada: Se ha detectado movimiento.'
        ]);

        $contactos = \App\Models\ContactoEmergencia::where('sucursal_id', $alarma->sucursal_id)->get();

        foreach ($contactos as $contacto) {
            // Registro inicial, antes de enviar
            $envio = \App\Models\envio::create([
                'evento_id'   => $evento->id,
                'contacto_id' => $contacto->id,
                'fecha_envio' => $fechaEnvio,
                'estado'      => 'Pendiente',
                'forma'       => 'Correo',
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

                // Aquí podrías registrar log o avisar al admin
            }
        }

        return response()->json(['message' => 'Alarma activada']);
    }

    public function desactivar($id)
    {
        $alarma = Alarma::findOrFail($id);
        $alarma->estado = 'Apagada';
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
}
