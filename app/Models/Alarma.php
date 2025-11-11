<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarma extends Model
{
    protected $table = 'alarma';

    protected $fillable = [
        'id',
        'nombre',
        'estado',
        'duracion',
        'h_encendido',
        'h_apagado',
        'sucursal_id',
        'updated_at'
    ];

    /**
     * Estados válidos de la alarma
     */
    public const ESTADO_APAGADA = 'Apagada';
    public const ESTADO_EN_ESPERA = 'En Espera';
    public const ESTADO_ACTIVA = 'Activa';


    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
    public function evento()
    {
        return $this->hasOne(Evento::class, 'alarma_id')->latest();
    }
}
