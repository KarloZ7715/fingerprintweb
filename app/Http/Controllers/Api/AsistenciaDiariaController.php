<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegistroSensor;
use Illuminate\Database\QueryException;
use Carbon\Carbon;

class AsistenciaDiariaController extends Controller
{
    public function store($huella_id)
    {
        try {
            // Insertar registro sin verificar existencia previa
            $registro = RegistroSensor::create([
                'huella_id'  => $huella_id,
                'fecha_hora' => Carbon::now(),
            ]);

            return response()->json([
                'success'  => true,
                'registro' => $registro,
            ], 201);

        } catch (QueryException $e) {
            // Si el trigger lanza SQLSTATE '45000', atrapa el mensaje y responde
            $sqlMessage = $e->getMessage();

            // Opcional: cambia el mensaje si detecta tu trigger
            if (strpos($sqlMessage, '45000') !== false) {
                if (strpos($sqlMessage, 'Ya completó entrada y salida hoy') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya preparado entrada y salida hoy',
                    ], 409);
                }
                if (strpos($sqlMessage, 'Sin horario asignado') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sin horario asignado',
                    ], 409);
                }
                // Puedes agregar más condiciones si tu trigger lanza otros mensajes
            }

            // Otros errores SQL
            return response()->json([
                'success' => false,
                'message' => 'Error en la base de datos: ' . $sqlMessage,
            ], 500);
        }
    }
}