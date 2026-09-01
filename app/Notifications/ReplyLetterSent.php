<?php

namespace App\Notifications;

use App\Models\ReplyLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['database', 'mail'];
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Surat balasan tersedia - SI-MELAYUR')
            ->view('emails.reply-letter-sent', [
                'participant' => $notifiable,
                'dashboardUrl' => route('peserta.dashboard') . '#surat-balasan',
            ], 'emails.reply-letter-sent-text');
    }
}