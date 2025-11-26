<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroSensor extends Model
{
    protected $table = 'registro_sensor';
    protected $fillable = [
        'huella_id',
        'fecha_hora',
    ];

    // Si tienes un modelo de Huella, puedes añadir la relación
    // public function huella(): BelongsTo
    // {
    //     return $this->belongsTo(Huella::class);
    // }
}