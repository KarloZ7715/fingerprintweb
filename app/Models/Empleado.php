<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleado'; 
    protected $fillable = [
        'cedula',
        'primer_nombre',
        'primer_apellido',
        'telefono',
        'estado',
        'sucursal_id',
    ];

    public function huella()
    {
        return $this->hasOne(Huella::class, 'empleado_id');
    }
}
