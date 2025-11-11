<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Evento extends Model
{
    public $timestamps = false; 

    protected $table = 'evento';

    protected $fillable = [
     'id', 'fecha_evento', 'alarma_id', 'Evento', 'Accion'
     // NO necesitas 'created_at' ni 'updated_a'
    ];

    public function alarma()
    {
        return $this->belongsTo(Alarma::class);
    }
    public function envios()
{
    return $this->hasMany(\App\Models\envio::class, 'evento_id');
}
public function contacto()
{
    return $this->belongsTo(\App\Models\ContactoEmergencia::class, 'contacto_id');
}
}