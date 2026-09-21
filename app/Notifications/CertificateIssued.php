<?php

namespace App\Notifications;

use App\Models\ParticipantApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

final class CertificateIssued extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ParticipantApplication $application,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'certificate_issued',
            'title' => 'Sertifikat telah terbit',
            'message' => 'Sertifikat untuk kegiatan '.$this->application->serviceLabel().' Anda telah terbit dan siap diunduh.',
            'application_id' => $this->application->id,
            'action_url' => route('peserta.dashboard').'#sertifikat',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $viewData = [
            'participant' => $notifiable,
            'application' => $this->application,
            'dashboardUrl' => route('peserta.dashboard').'#sertifikat',
        ];

        $message = (new MailMessage)
            ->subject('Sertifikat Telah Terbit - SI-MELAYUR')
            ->view('emails.certificate-issued', $viewData)
            ->text('emails.certificate-issued-text', $viewData);

        if (
            filled($this->application->certificate_path)
            && Storage::disk('local')->exists($this->application->certificate_path)
        ) {
            $message->attach(
                Storage::disk('local')->path($this->application->certificate_path),
                [
                    'as' => 'Sertifikat - '.$this->application->participant->name.'.pdf',
                    'mime' => 'application/pdf',
                ],
            );
        }

        return $message;
    }
}