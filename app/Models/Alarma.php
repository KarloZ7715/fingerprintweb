<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarma extends Model
{
   // Indica la tabla 
    protected $table = 'alarma';

    // Si tu tabla NO tiene los campos típicos de Laravel (updated_at, etc.), puedes indicarlo:
    public $timestamps = false;

    // Aquí puedes añadir los campos que quieras usar como fillable
    protected $fillable = [
         'codigo',
        'nombre',
        'estado',
        'Duracion',
        'h_encendido',
        'h_apagado',
        'sucursal_id',
    ];
}
class Alarma  extends Model
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


    public function sucursal()
{
    return $this->belongsTo(Sucursal::class);
}
}
