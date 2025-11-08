<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class envio extends Model
{
    // Para que Laravel gestione 'created_at' y 'updated_at'
    public $timestamps = true;

    protected $table = 'envio';

    protected $fillable = [
        'evento_id',
        'contacto_id',
        'fecha_envio',
        'estado',
        'forma'
    ];

    // Relaciones (si tienes más)
    public function mensaje_emergencia()
    {
        return $this->belongsTo(mensaje_emergencia::class);
    }

    // Si tienes relación con Evento
    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    // Si tienes relación con ContactoEmergencia
    public function contacto()
    {
        return $this->belongsTo(ContactoEmergencia::class, 'contacto_id');
    }
}