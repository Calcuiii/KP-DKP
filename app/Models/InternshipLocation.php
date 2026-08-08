<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class InternshipLocation extends Model
{
    public const QUOTA_AVAILABLE = 'available';
    public const QUOTA_LIMITED = 'limited';
    public const QUOTA_FULL = 'full';
    public const QUOTA_UNAVAILABLE = 'unavailable';
    public const QUOTA_UNKNOWN = 'unknown';

    protected $fillable = ['name', 'quota_status', 'display_order', 'quota_updated_at'];

    protected function casts(): array
    {
        return ['quota_updated_at' => 'datetime'];
    }

    public function quotaLabel(): string
    {
        return match ($this->quota_status) {
            self::QUOTA_AVAILABLE => 'Kuota tersedia',
            self::QUOTA_LIMITED => 'Kuota terbatas',
            self::QUOTA_FULL => 'Kuota penuh',
            self::QUOTA_UNAVAILABLE => 'Tidak menerima',
            default => 'Belum diperbarui',
        };
    }
}
