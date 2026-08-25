<?php

namespace App\Notifications;

use App\Models\ReplyLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class ReplyLetterSent extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ReplyLetter $replyLetter,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reply_letter',

            'title' => 'Surat balasan tersedia',

            'message' => 'Admin telah mengirimkan surat balasan untuk pengajuan Anda. Silakan buka surat balasan pada dashboard.',

            'status' => 'reply_letter_sent',

            'reply_letter_id' => $this->replyLetter->id,

            'action_url' => route('peserta.dashboard') . '#surat-balasan',
        ];
    }
}