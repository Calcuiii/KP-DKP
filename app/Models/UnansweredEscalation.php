<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UnansweredEscalation extends Model
{
    protected $fillable = [
        'assistant_message_id',
        'user_message_id',
        'ticket_code',
        'status',
        'admin_response',
        'response_message_id',
        'responded_at',
        'whatsapp_status',
        'whatsapp_error',
        'whatsapp_sent_at',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'whatsapp_sent_at' => 'datetime',
            'responded_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function assistantMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'assistant_message_id');
    }

    public function userMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'user_message_id');
    }

    public function responseMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'response_message_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
