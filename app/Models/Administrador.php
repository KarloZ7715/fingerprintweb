<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Administrador extends Authenticatable implements FilamentUser, HasName
{
    use Notifiable;

    protected $table = 'administrador';

    protected $fillable = [
        'id',
        'cedula',
        'primer_nombre',
        'primer_apellido',
        'email',
        'telefono',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = 'updated_at';

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentName(): string
    {
        $fullName = trim(sprintf('%s %s', $this->primer_nombre ?? '', $this->primer_apellido ?? ''));

        return $fullName !== '' ? $fullName : ($this->email ?? (string) $this->cedula);
    }
}
