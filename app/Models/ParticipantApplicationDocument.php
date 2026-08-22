<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ParticipantApplicationDocument extends Model
{
    public const TYPE_GUESTBOOK = 'guestbook_proof';

    public const TYPE_REQUEST_LETTER = 'request_letter';

    public const TYPE_ETHICS_APPROVAL = 'ethics_approval';

    public const TYPE_WOPPS_FORM_PROOF = 'wopps_form_proof';

    public const TYPE_INTERNSHIP_FORM_PROOF = 'internship_form_proof';

    public const REVIEW_SUBMITTED = 'submitted';

    public const REVIEW_REVISION = 'revision_required';

    public const REVIEW_APPROVED = 'approved';

    protected $fillable = ['type', 'version', 'file_path', 'original_name', 'mime_type', 'file_size', 'review_status', 'review_notes', 'reviewed_at', 'automated_check_status', 'automated_check_results', 'automated_checked_at'];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'automated_checked_at' => 'datetime',
            'automated_check_results' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ParticipantApplication::class, 'participant_application_id');
    }
}
