<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoEmergencia extends Model
{
    
    protected $table = 'contacto_emergencia';
    protected $fillable = [
        'nombre_completo',
        'telefono',
        'correo',
        'usario_tele',
        'sucursal_id',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
