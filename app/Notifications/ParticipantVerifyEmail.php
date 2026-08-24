<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

final class ParticipantVerifyEmail extends VerifyEmail
{
    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi akun peserta SI-MELAYUR')
            ->view('emails.participant-verify-email', [
                'participant' => $notifiable,
                'verificationUrl' => $verificationUrl,
            ], 'emails.participant-verify-email-text');
    }
}
