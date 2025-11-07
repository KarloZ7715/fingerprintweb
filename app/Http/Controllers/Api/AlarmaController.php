<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Alarma;
use App\Models\Evento;

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
        // Crea evento
        Evento::create([
            'alarma_id' => $alarma->id,
            'fecha_evento' => now(),
            'Evento' => 'Activar',
            'Accion' => 'Alarma activada por API'
            
        ]);
        return response()->json(['message' => 'Alarma activada']);
    }

    public function desactivar($id)
    {
        $alarma = Alarma::findOrFail($id);
        $alarma->estado = 'Inactiva';
        $alarma->save();
        // Crea evento
        Evento::create([
            'alarma_id' => $alarma->id,
            'fecha_evento' => now(),
            'Evento' => 'Desactivar',
            'Accion' => 'Alarma desactivada por API'
        ]);
        return response()->json(['message' => 'Alarma desactivada']);
    }
}