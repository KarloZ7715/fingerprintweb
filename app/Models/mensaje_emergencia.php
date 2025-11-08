<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mensaje_emergencia extends Model
{
    public $timestamps = false; 

    protected $table = 'mensaje_emergencia';

    protected $fillable = [
 'id', 'mensaje', 'Forma', 'created_at'
    ];

    public function envio()
    {
        return $this->belongsTo(envio::class);
    }
}
