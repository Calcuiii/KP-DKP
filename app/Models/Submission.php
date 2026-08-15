<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    public const STEP_LABELS = [
        1 => 'Menunggu Verifikasi Administrasi',
        2 => 'Dokumen Sedang Ditinjau',
        3 => 'Menunggu Surat Balasan',
        4 => 'Pengajuan Diterima',
        5 => 'Pelaksanaan KP/Magang/WOPPS',
        6 => 'Pengumpulan Laporan Akhir',
        7 => 'Sertifikat Diproses',
        8 => 'Selesai',
    ];

    protected $fillable = [
        'user_id', 'reference_number', 'type', 'title',
        'step', 'step_label', 'action_state', 'revision_note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->step < 8;
    }
}