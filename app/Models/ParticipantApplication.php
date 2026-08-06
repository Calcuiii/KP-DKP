<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'google_form_confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Participant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
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
            default => 'https://bit.ly/DaftarMagangPKL_DKP_JATIM',
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
