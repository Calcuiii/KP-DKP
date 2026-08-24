<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReplyLetter extends Model
{
    protected $fillable = [
        'participant_id',
        'file_path',
        'original_name',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(
            Participant::class,
            'participant_id'
        );
    }
}