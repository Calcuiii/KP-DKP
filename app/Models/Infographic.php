<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Infographic extends Model
{
    public const TYPE_INFOGRAPHIC = 'infografis';

    public const TYPE_WOPPS = 'infografis_wopps';

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

    public function getServiceLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_WOPPS => 'WOPPS',
            self::TYPE_INFOGRAPHIC => 'Magang / KP / PKL',
            default => 'Dokumen Resmi',
        };
    }

    public function getDisplayLabelAttribute(): string
    {
        if ($this->type === 'surat_resmi') {
            return 'Surat Edaran Resmi';
        }

        return sprintf('%s · Seri %02d/07', $this->service_label, $this->series_number);
    }

    public function getServiceDescriptionAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_WOPPS => 'Panduan visual layanan WOPPS',
            self::TYPE_INFOGRAPHIC => 'Panduan visual layanan Magang, KP, dan PKL',
            default => 'Dokumen resmi Pemerintah Provinsi Jawa Timur',
        };
    }
}
