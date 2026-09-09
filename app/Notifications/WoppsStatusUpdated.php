<?php

namespace App\Notifications;

use App\Models\ParticipantApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class WoppsStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ParticipantApplication $application,
        private readonly string $event,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        [$title, $message] = match ($this->event) {
            'contacted' => ['Peserta telah dihubungi', 'Petugas Dinas telah menandai bahwa Anda sudah dihubungi untuk tindak lanjut layanan WOPPS.'],
            'completed' => ['Layanan WOPPS selesai', 'Layanan WOPPS Anda telah selesai. Anda dapat membuat pengajuan layanan baru dari portal peserta.'],
            default => ['Pembaruan layanan WOPPS', 'Status layanan WOPPS Anda telah diperbarui oleh petugas Dinas.'],
        };

        return [
            'type' => 'wopps_status_updated',
            'title' => $title,
            'message' => $message,
            'status' => $this->application->status,
            'application_id' => $this->application->id,
            'action_url' => route('peserta.dashboard').'#tindak-lanjut-wopps',
        ];
    }
}
