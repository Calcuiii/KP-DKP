<?php

namespace App\Notifications;

use App\Models\ParticipantApplicationDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class DocumentReviewUpdated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ParticipantApplicationDocument $document,
        private readonly string $decision,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $isApproved = $this->decision === ParticipantApplicationDocument::REVIEW_APPROVED;
        $isEthics = $this->document->type === ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL;
        $documentName = $isEthics ? 'Ethics Approval Statement Letter' : 'surat permohonan';

        return [
            'title' => $isApproved ? 'Dokumen disetujui' : 'Perbaikan dokumen diperlukan',
            'message' => $isApproved
                ? ucfirst($documentName).' Anda telah disetujui admin. Silakan lanjutkan ke tahap berikutnya.'
                : 'Admin meminta perbaikan pada '.$documentName.' Anda.',
            'review_notes' => $this->document->review_notes,
            'status' => $isApproved ? 'approved' : 'revision_required',
            'document_type' => $this->document->type,
            'application_id' => $this->document->participant_application_id,
            'action_url' => route('peserta.dashboard').'#persiapan',
        ];
    }
}
