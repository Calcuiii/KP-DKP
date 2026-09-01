<?php

namespace App\Notifications;

use App\Models\ParticipantApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class InternshipFormSubmitted extends Notification
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
            'type' => 'internship_form_submitted',
            'title' => 'Google Form magang/PKL telah diisi',
            'message' => $participantName.' telah mengonfirmasi pengisian Google Form magang/PKL. Silakan cek tabel pada menu Surat Balasan dan periksa data spreadsheet pengisian dari peserta tersebut.',
            'application_id' => $this->application->id,
            'action_url' => route('admin.surat-balasan'),
        ];
    }
}