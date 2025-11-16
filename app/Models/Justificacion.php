<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Justificacion extends Model
{
    protected $table = 'justificacion';
    protected $fillable = [
        'asistencia_diaria_id',
        'empleado_id',
        'tipo',
        'motivo',
        'estado',
        'aprobado_por',
        'fecha_aprobacion',
    ];

    // Relación a AsistenciaDiaria
    public function asistenciaDiaria(): BelongsTo
    {
        return $this->belongsTo(AsistenciaDiaria::class);
    }

    // Relación a Empleado
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}