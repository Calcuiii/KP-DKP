<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

final class ParticipantResetPassword extends ResetPassword
{
    public function toMail(mixed $notifiable): MailMessage
    {
        $resetUrl = route('peserta.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Atur ulang kata sandi peserta SI-MELAYUR')
            ->view('emails.participant-reset-password', [
                'participant' => $notifiable,
                'resetUrl' => $resetUrl,
                'expiresInMinutes' => config('auth.passwords.participants.expire', 60),
            ], 'emails.participant-reset-password-text');
    }
}
