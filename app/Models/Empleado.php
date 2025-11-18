<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleado';

    protected $fillable = [
        'cedula',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'codigo_pais',
        'telefono',
        'email',
        'estado',
        'sucursal_id',
        'horario_id',
        'foto_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con Sucursal
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Relación con Horario
     */
    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }

    /**
     * Relación con Huellas
     */
    public function huellas(): HasMany
    {
        return $this->hasMany(Huella::class, 'empleado_id');
    }

    /**
     * Relación con Asistencias
     */
    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'empleado_id');
    }

    /**
     * Obtener nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim(
            $this->primer_nombre . ' ' .
            ($this->segundo_nombre ?? '') . ' ' .
            $this->primer_apellido . ' ' .
            ($this->segundo_apellido ?? '')
        );
    }

    /**
     * Obtener teléfono completo con código de país
     */
    public function getTelefonoCompletoAttribute(): string
    {
        return '+' . $this->codigo_pais . ' ' . $this->telefono;
    }

    /**
     * Verificar si el empleado tiene huella registrada
     */
    public function tieneHuella(): bool
    {
        return $this->huellas()
            ->where('estado', 'Activa')
            ->exists();
    }

    /**
     * Obtener la huella activa del empleado
     */
    public function huellaActiva(): ?Huella
    {
        return $this->huellas()
            ->where('estado', 'Activa')
            ->first();
    }

    /**
     * Scope para empleados pendientes de huella
     */
    public function scopePendientesHuella($query)
    {
        return $query->where('estado', 'Pendiente_Huella');
    }

    /**
     * Scope para empleados activos con huella
     */
    public function scopeActivosConHuella($query)
    {
        return $query->where('estado', 'Activo')
            ->whereHas('huellas', function ($q) {
                $q->where('estado', 'Activa');
            });
    }
}
