<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ChatMessage extends Model
{
    use HasFactory;

    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = [
        'chat_conversation_id',
        'role',
        'content',
        'status',
        'response_time_ms',
    ];

    protected function casts(): array
    {
        return [
            'response_time_ms' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(ChatMessageSource::class)->orderBy('position');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(ChatFeedback::class);
    }
}
