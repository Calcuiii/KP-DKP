<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationLog extends Model
{
    protected $fillable = [
    'code', 'question', 'answer_preview', 'category', 'status',
    'sources', 'score', 'response_time',
    ];
    protected $casts = [
        'score' => 'decimal:2',
        'response_time' => 'decimal:2',
    ];
}