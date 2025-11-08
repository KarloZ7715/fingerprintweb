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

        // 1. Crea el evento
        $evento = Evento::create([
            'alarma_id' => $alarma->id,
            'fecha_evento' => $fechaEnvio,
            'Evento' => 'Activar',
            'Accion' => 'Alarma activada por API'
        ]);

        // 2. Busca todos los contactos de la sucursal
        $contactos = \App\Models\ContactoEmergencia::where('sucursal_id', $alarma->sucursal_id)->get();

        // 3. Crea un registro de envio y envía correo para cada contacto
        foreach ($contactos as $contacto) {
            \App\Models\envio::create([
                'evento_id'   => $evento->id,
                'contacto_id' => $contacto->id,
                'fecha_envio' => $fechaEnvio,
                'estado'      => 'Pendiente',
                'forma'       => 'Correo',
            ]);

            // Enviar el correo a cada contacto de emergencia
            Mail::to($contacto->correo)
                ->send(new NotificacionAlarma(
                    '¡Alarma activada!',
                    $alarma,
                    $evento
                ));
        }

        return response()->json(['message' => 'Alarma activada']);
    }

    public function desactivar($id)
    {
        $alarma = Alarma::findOrFail($id);
        $alarma->estado = 'Inactiva';
        $alarma->save();

        $fechaEnvio = Carbon::now('America/Bogota');

        // 1. Crea el evento
        $evento = Evento::create([
            'alarma_id' => $alarma->id,
            'fecha_evento' => $fechaEnvio,
            'Evento' => 'Desactivar',
            'Accion' => 'Alarma desactivada por API'
        ]);

        // 2. Busca todos los contactos de la sucursal
        $contactos = \App\Models\ContactoEmergencia::where('sucursal_id', $alarma->sucursal_id)->get();

        // 3. Crea un registro de envio y envía correo para cada contacto
        foreach ($contactos as $contacto) {
            \App\Models\envio::create([
                'evento_id'   => $evento->id,
                'contacto_id' => $contacto->id,
                'fecha_envio' => $fechaEnvio,
                'estado'      => 'Pendiente',
                'forma'       => 'Correo',
            ]);

            // Enviar el correo a cada contacto de emergencia
            Mail::to($contacto->correo)
                ->send(new NotificacionAlarma(
                    '¡Alarma desactivada!',
                    $alarma,
                    $evento 
                ));
        }

        return response()->json(['message' => 'Alarma desactivada']);
    }
}