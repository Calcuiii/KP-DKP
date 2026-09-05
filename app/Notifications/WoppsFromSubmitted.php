<?php

namespace App\Notifications;

use App\Models\ParticipantApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class WoppsFormSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ParticipantApplication $application,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $participantName = $this->application->participant?->name ?? 'Peserta';

        return [
            'type' => 'wopps_form_submitted',
            'title' => 'Pengajuan WOPPS selesai',
            'message' => $participantName.' telah mengisi Form Pengajuan WOPPS. Mohon ditindaklanjuti: cek spreadsheet hasil pengisian dan hubungi peserta melalui WhatsApp.',
            'application_id' => $this->application->id,
            'action_url' => route('admin.wopps-follow-up'),
        ];
    }
}