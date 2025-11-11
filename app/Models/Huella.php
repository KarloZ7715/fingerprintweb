<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo básico de Huella
 */
class Huella extends Model
{
    use HasFactory;

    protected $table = 'huella';

    protected $fillable = [
        'numero_slot',
        'tipo_dedo',
        'mano',
        'empleado_id',
        'fecha_enrolamiento',
        'enrolado_por',
        'calidad',
        'estado',
    ];

    protected $casts = [
        'numero_slot' => 'integer',
        'fecha_enrolamiento' => 'datetime',
        'enrolado_por' => 'integer',
        'calidad' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con Empleado
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Relación con Administrador que enroló
     */
    public function enroladoPor(): BelongsTo
    {
        return $this->belongsTo(Administrador::class, 'enrolado_por');
    }

    /**
     * Scope para huellas activas
     */
    public function scopeActiva($query)
    {
        return $query->where('estado', 'Activa');
    }
}
