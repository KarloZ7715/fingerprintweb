<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo básico de Horario
 */
class Horario extends Model
{
    use HasFactory;

    protected $table = 'horario';

    protected $fillable = [
        'nombre',
        'descripcion',
        'hora_entrada',
        'hora_salida',
        'tolerancia_entrada',
        'tolerancia_salida',
        'dias_laborables',
        'requiere_entrada',
        'requiere_salida',
        'activo',
        'sucursal_id',
    ];

    protected $casts = [
        'hora_entrada' => 'datetime:H:i:s',
        'hora_salida' => 'datetime:H:i:s',
        'tolerancia_entrada' => 'integer',
        'tolerancia_salida' => 'integer',
        'dias_laborables' => 'array',
        'requiere_entrada' => 'boolean',
        'requiere_salida' => 'boolean',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';
    protected $timezone = 'America/Bogota';

    /**
     * Relación con Empleados
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'horario_id');
    }

    /**
     * Relación con Sucursal
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Scope para horarios activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
