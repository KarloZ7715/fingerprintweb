<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Facades\Filament;

class ResetPassword extends ResetPasswordNotification
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Restablecer contraseña de administrador')
            ->line('Recibiste este correo porque solicitaste un restablecimiento de contraseña para tu cuenta.')
            ->action('Restablecer contraseña', $this->resetUrl($notifiable))
            ->line('Si no solicitaste un restablecimiento de contraseña, no es necesario realizar ninguna acción.');
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        return Filament::getResetPasswordUrl($this->token, $notifiable);
    }
}
