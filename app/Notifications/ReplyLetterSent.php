<?php

namespace App\Notifications;

use App\Models\ParticipantApplication;
use App\Models\ReplyLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

final class ReplyLetterSent extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ReplyLetter $replyLetter,
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
        $accepted = $this->application->decision === 'accepted';

        return [
            'type' => 'reply_letter',

            'title' => $accepted ? 'Pengajuan diterima' : 'Pengajuan belum dapat diterima',

            'message' => $accepted
                ? 'Pengajuan Anda diterima untuk periode '.$this->periodLabel().'. Surat balasan tersedia pada dashboard.'
                : 'Pengajuan Anda belum dapat diterima. Silakan periksa surat balasan resmi pada dashboard.',

            'status' => $accepted ? 'accepted' : 'rejected',

            'reply_letter_id' => $this->replyLetter->id,

            'action_url' => route('peserta.dashboard').'#surat-balasan',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $viewData = [
            'participant' => $notifiable,
            'application' => $this->application,
            'accepted' => $this->application->decision === 'accepted',
            'periodLabel' => $this->periodLabel(),
            'dashboardUrl' => route('peserta.dashboard').'#surat-balasan',
        ];

        $message = (new MailMessage)
            ->subject(($this->application->decision === 'accepted' ? 'Pengajuan diterima' : 'Keputusan pengajuan').' - SI-MELAYUR')
            ->view('emails.reply-letter-sent', $viewData)
            ->text('emails.reply-letter-sent-text', $viewData);

        if (
            filled($this->replyLetter->file_path)
            && Storage::disk('public')->exists($this->replyLetter->file_path)
        ) {
            $message->attach(
                Storage::disk('public')->path($this->replyLetter->file_path),
                [
                    'as' => $this->replyLetter->original_name ?? 'surat-balasan.pdf',
                    'mime' => 'application/pdf',
                ],
            );
        }

        return $message;
    }

    private function periodLabel(): string
    {
        if (! $this->application->official_started_at || ! $this->application->official_ended_at) {
            return '-';
        }

        return $this->application->official_started_at->translatedFormat('d F Y')
            .' sampai '
            .$this->application->official_ended_at->translatedFormat('d F Y');
    }
}
