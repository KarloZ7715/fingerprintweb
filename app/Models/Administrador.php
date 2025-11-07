<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Panel;

class Administrador extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $table = 'administrador';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Puedes restringir acceso si quieres, por ahora lo dejamos en true
        return true;
    }
}
