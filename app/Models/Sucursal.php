<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursal';

    protected $fillable = [
        'nombre',
        'direccion',
        'administrador_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con Administrador
     */
    public function administrador(): BelongsTo
    {
        return $this->belongsTo(Administrador::class, 'administrador_id');
    }

    /**
     * Relación con Empleados
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'sucursal_id');
    }

    /**
     * Relación con Horarios
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'sucursal_id');
    }

    /**
     * Accessor para obtener nombre del administrador
     */
    public function getAdministradorNombreAttribute(): string
    {
        return $this->administrador ? $this->administrador->getFilamentName() : 'Sin asignar';
    }

    /**
     * Scope para sucursales activas
     */
    public function scopeActivo($query)
    {
        return $query->whereNotNull('administrador_id');
    }
}
