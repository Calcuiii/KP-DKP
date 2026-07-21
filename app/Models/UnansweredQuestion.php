<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnansweredQuestion extends Model
{
    protected $fillable = [
        'question', 'category', 'frequency', 'score',
        'first_asked', 'last_asked', 'status', 'fallback_response',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'first_asked' => 'date',
        'last_asked' => 'date',
    ];
}