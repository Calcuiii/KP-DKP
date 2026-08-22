<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParticipantApplication extends Model
{
    use HasFactory;

    public const SERVICE_MAGANG_PKL = 'magang_pkl';

    public const SERVICE_WOPPS = 'wopps';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'service_type',
        'status',
        'guestbook_confirmed_at',
        'letter_submitted_at',
        'google_form_confirmed_at',
        'official_started_at',
        'official_ended_at',
        'decision',
        'response_letter_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'google_form_confirmed_at' => 'datetime',
            'guestbook_confirmed_at' => 'datetime',
            'letter_submitted_at' => 'datetime',
            'official_started_at' => 'datetime',
            'official_ended_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Participant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ParticipantApplicationDocument::class);
    }

    public function latestDocument(string $type): ?ParticipantApplicationDocument
    {
        return $this->documents->where('type', $type)->sortByDesc('version')->first();
    }

    public function requestLetterApproved(): bool
    {
        return $this->latestDocument(ParticipantApplicationDocument::TYPE_REQUEST_LETTER)?->review_status === ParticipantApplicationDocument::REVIEW_APPROVED;
    }

    /**
     * @return array<string, array{label: string, description: string}>
     */
    public static function serviceOptions(): array
    {
        return [
            self::SERVICE_MAGANG_PKL => [
                'label' => 'Kerja Praktik, Magang, atau PKL',
                'description' => 'Persiapan administrasi untuk kegiatan magang atau praktik kerja lapangan.',
            ],
            self::SERVICE_WOPPS => [
                'label' => 'WOPPS',
                'description' => 'Wawancara, observasi, penelitian, pendataan, survei, dan layanan terkait.',
            ],
        ];
    }

    public function serviceLabel(): string
    {
        return self::serviceOptions()[$this->service_type]['label'];
    }

    public function googleFormUrl(): string
    {
        return match ($this->service_type) {
            self::SERVICE_WOPPS => 'https://bit.ly/WOPPS',
            default => 'https://tinyurl.com/DaftarMagangDKP-PT',
        };
    }

    /**
     * @return array<string, string>
     */
    public function googleFormOptions(): array
    {
        return match ($this->service_type) {
            self::SERVICE_WOPPS => [
                'Buka Google Form WOPPS' => 'https://bit.ly/WOPPS',
            ],
            default => [
                'SMA/SMK' => 'https://tinyurl.com/DaftarMagangDKP-SM',
                'Perguruan Tinggi' => 'https://tinyurl.com/DaftarMagangDKP-PT',
            ],
        };
    }

    /**
     * @return list<array{label: string, description: string}>
     */
    public function preparationChecklist(): array
    {
        return match ($this->service_type) {
            self::SERVICE_WOPPS => [
                ['label' => 'Identitas diri yang masih berlaku', 'description' => 'KTM, KTP, atau SIM.'],
                ['label' => 'Surat permohonan resmi', 'description' => 'Dari institusi pendidikan atau instansi asal.'],
                ['label' => 'Proposal kegiatan', 'description' => 'Sesuai kebutuhan layanan.'],
                ['label' => 'Persetujuan etik', 'description' => 'Bila dipersyaratkan untuk kegiatan.'],
            ],
            default => [
                ['label' => 'Buku Tamu Magang / PKL', 'description' => 'Diisi secara individu sebagai pendataan awal.'],
                ['label' => 'Koordinasi kuota', 'description' => 'Konfirmasi ketersediaan kuota dan kesesuaian jurusan.'],
                ['label' => 'Surat permohonan resmi', 'description' => 'Dari sekolah atau perguruan tinggi, dengan informasi lengkap.'],
                ['label' => 'Kebutuhan sertifikat', 'description' => 'Dicantumkan sejak awal bila diperlukan.'],
            ],
        };
    }
}
