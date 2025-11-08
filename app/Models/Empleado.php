<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empleado extends Model
{
    use HasFactory;

    // The migrations create the table as 'employees' (English plural). Map the model to that table.
    protected $table = 'employees';

    protected $fillable = [
        'cedula',
        'primer_nombre',
        'primer_apellido',
        'telefono',
        'estado',
        'sucursal_id',
    ];

    public function horarios(): BelongsToMany
    {
        return $this->belongsToMany(Horario::class, 'empleado_horario', 'empleado_id', 'horario_id');
    }

    public function huella()
    {
        return $this->hasOne(Huella::class, 'empleado_id');
    }
}
