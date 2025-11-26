<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use App\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Log;

class Administrador extends Authenticatable implements FilamentUser, HasName, CanResetPassword
{
    use Notifiable, CanResetPasswordTrait;

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

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
