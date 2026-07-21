<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBaseDocument extends Model
{
    protected $fillable = [
        'name', 'category', 'type', 'version', 'description',
        'effective_date', 'file_path', 'chunks_count',
        'index_status', 'status', 'uploaded_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}