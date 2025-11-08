<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class envio extends Model
{
    public $timestamps = false; 

    protected $table = 'envio';

    protected $fillable = [
    'id', 'mensaje_id', 'contacto_id', 'fecha_envio', 'estado'
    ];

    public function mensaje_emergencia()
    {
        return $this->belongsTo(mensaje_emergencia::class);
    }
}