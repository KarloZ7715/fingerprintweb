<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo básico de Asistencia
 */
class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencia';

    protected $fillable = [
        'empleado_id',
        'horario_id',
        'fecha',
        'hora_registro',
        'huella_id',
        'estado',
        'tipo',
        'minutos_diferencia',
        'observaciones',
        'justificada',
        'motivo_justificacion',
        'justificado_por',
        'fecha_justificacion',
        'metodo_registro',
        'fecha_hora_registro',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'hora_registro' => 'datetime:Y-m-d H:i:s',
        'minutos_diferencia' => 'integer',
        'justificada' => 'boolean',
        'fecha_justificacion' => 'datetime',
        'fecha_hora_registro' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Relación con Empleado
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Relación con Horario
     */
    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }

    /**
     * Relación con Huella
     */
    public function huella(): BelongsTo
    {
        return $this->belongsTo(Huella::class, 'huella_id');
    }

    /**
     * Relación con Administrador que justificó
     */
    public function justificadoPor(): BelongsTo
    {
        return $this->belongsTo(Administrador::class, 'justificado_por');
    }

    /**
     * Scope para asistencias no justificadas
     */
    public function scopeSinJustificar($query)
    {
        return $query->where('justificada', false);
    }

    /**
     * Scope para asistencias por fecha
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Accesor para nombre completo del empleado
     */
    public function getNombreEmpleadoAttribute(): string
    {
        return $this->empleado ? 
            "{$this->empleado->primer_nombre} {$this->empleado->primer_apellido}" : 
            'N/A';
    }

    /**
     * Accessor: hora_entrada (alias de hora_registro)
     */
    public function getHoraEntradaAttribute()
    {
        return $this->hora_registro ? \Carbon\Carbon::parse($this->hora_registro)->format('H:i:s') : null;
    }

    /**
     * Accessor: minutos_retraso (alias de minutos_diferencia absoluto)
     */
    public function getMinutosRetrasoAttribute()
    {
        return abs($this->minutos_diferencia ?? 0);
    }

    /**
     * Accessor: justificado (alias de justificada)
     */
    public function getJustificadoAttribute()
    {
        return (bool) $this->attributes['justificada'];
    }

    /**
     * Accessor: justificacion (alias de motivo_justificacion)
     */
    public function getJustificacionAttribute()
    {
        return $this->motivo_justificacion;
    }
}
