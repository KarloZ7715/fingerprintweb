<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsistenciaDiaria extends Model
{
    protected $table = 'asistencia_diaria';
    protected $fillable = [
        'empleado_id',
        'horario_id',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'minutos_retraso',
        'horas_trabajadas',
        'estado',
    ];

    // Relación a Empleado
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    // Relación a Horario
    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    // Justificaciones
    public function justificaciones(): HasMany
    {
        return $this->hasMany(Justificacion::class);
    }
}