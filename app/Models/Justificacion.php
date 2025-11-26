<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Justificacion extends Model
{
    protected $table = 'justificacion';
    protected $fillable = [
        'empleado_id',
        'tipo',
        'motivo',
        'estado',
        'aprobado_por',
        'fecha_aprobacion',
        'plazo_dias',
        'fecha_expiracion',
      'fecha_incapacidad',
    ];

    // Relación a AsistenciaDiaria
    public function asistenciaDiaria(): BelongsTo
    {
        return $this->belongsTo(AsistenciaDiaria::class);
    }
    // Relación al Administrador que aprobó
public function administrador()
{
    return $this->belongsTo(Administrador::class, 'aprobado_por');
}
    // Relación a Empleado
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
