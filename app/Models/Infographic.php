<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Infographic extends Model
{
    protected $fillable = [
        'caption',
        'alt',
        'image_path',
        'image_width',
        'image_height',
    ];

    protected $casts = [
        'position' => 'integer',
        'series_number' => 'integer',
        'image_width' => 'integer',
        'image_height' => 'integer',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }

    public function getImageUrlAttribute(): string
    {
        return asset($this->image_path);
    }
}
