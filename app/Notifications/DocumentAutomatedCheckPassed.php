<?php

namespace App\Notifications;

use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class DocumentAutomatedCheckPassed extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ParticipantApplicationDocument $document,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $this->document->loadMissing('application.participant');

        $participantName = $this->document->application?->participant?->name ?? 'Peserta';
        $serviceType = $this->document->application?->service_type;

        $documentLabel = match (true) {
            $this->document->type === ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL
                => 'Ethics Approval Statement Letter',

            $serviceType === ParticipantApplication::SERVICE_WOPPS
                => 'surat permohonan WOPPS',

            default
                => 'surat permohonan magang/PKL',
        };

        return [
            'type' => 'document_automated_check_passed',
            'title' => 'Dokumen lolos pengecekan otomatis',
            'message' => 'Dokumen '.$documentLabel.' dari '.$participantName.' telah lolos pengecekan otomatis. Mohon ditindaklanjuti.',
            'document_id' => $this->document->id,
            'action_url' => route('admin.pemeriksaan-dokumen.show', $this->document),
        ];
    }
}