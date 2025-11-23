<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Password;
use App\Notifications\Auth\ResetPassword as ResetPasswordNotification;

class CustomRequestPasswordReset extends BaseRequestPasswordReset
{
    public function request(): void
    {
        $data = $this->form->getState();

        $status = Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
            $this->getCredentialsFromFormData($data),
            function ($user, $token) {
                $user->notify(new ResetPasswordNotification($token));
            }
        );

        if ($status !== Password::RESET_LINK_SENT) {
            $this->getFailureNotification($status)?->send();

            return;
        }

        $this->getSentNotification($status)?->send();

        $this->form->fill();
    }
}
