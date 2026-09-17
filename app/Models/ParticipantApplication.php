<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'pic_contacted_at',
        'completed_at',
        'official_started_at',
        'official_ended_at',
        'decision',
        'response_letter_path',

        /*
        |--------------------------------------------------------------------------
        | Tindak lanjut sertifikat
        |--------------------------------------------------------------------------
        */
        'certificate_follow_up_choice',
        'certificate_follow_up_at',

        /*
        |--------------------------------------------------------------------------
        | Penyelesaian & Penerbitan Sertifikat
        |--------------------------------------------------------------------------
        */
        'completion_checklist',
        'presentation_date',
        'presentation_photo_path',
        'presentation_submitted_at',
        'completion_form_proof_path',
        'completion_form_submitted_at',
        'certificate_path',
        'certificate_published_at',
        'certificate_emailed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completion_checklist' => 'array',
            'presentation_date' => 'date',
            'presentation_submitted_at' => 'datetime',
            'completion_form_submitted_at' => 'datetime',
            'certificate_published_at' => 'datetime',
            'certificate_emailed_at' => 'datetime',
            'google_form_confirmed_at' => 'datetime',
            'pic_contacted_at' => 'datetime',
            'completed_at' => 'datetime',
            'guestbook_confirmed_at' => 'datetime',
            'letter_submitted_at' => 'datetime',
            'official_started_at' => 'datetime',
            'official_ended_at' => 'datetime',

            /*
            |--------------------------------------------------------------------------
            | Tanggal peserta memilih tindak lanjut sertifikat
            |--------------------------------------------------------------------------
            */
            'certificate_follow_up_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Participant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function replyLetter(): HasOne
    {
        return $this->hasOne(
            ReplyLetter::class,
            'participant_application_id'
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(
            ParticipantApplicationDocument::class
        );
    }

    public function latestDocument(
        string $type
    ): ?ParticipantApplicationDocument {
        return $this->documents
            ->where('type', $type)
            ->sortByDesc('version')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | SURAT PERMOHONAN
    |--------------------------------------------------------------------------
    */

    public function requestLetterApproved(): bool
    {
        return $this->latestDocument(
            ParticipantApplicationDocument::TYPE_REQUEST_LETTER
        )?->review_status
            === ParticipantApplicationDocument::REVIEW_APPROVED;
    }

    /*
    |--------------------------------------------------------------------------
    | ETHICS APPROVAL
    |--------------------------------------------------------------------------
    */

    public function ethicsApprovalApproved(): bool
    {
        return $this->latestDocument(
            ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL
        )?->review_status
            === ParticipantApplicationDocument::REVIEW_APPROVED;
    }

    /*
    |--------------------------------------------------------------------------
    | CEK APAKAH PESERTA HARUS MEMILIH TINDAK LANJUT SERTIFIKAT
    |--------------------------------------------------------------------------
    */

    public function certificateFollowUpRequired(): bool
    {
        $letter = $this->latestDocument(
            ParticipantApplicationDocument::TYPE_REQUEST_LETTER
        );

        return $letter?->review_status
            === ParticipantApplicationDocument::REVIEW_APPROVED
            && $letter->certificate_eligible === false
            && $this->certificate_follow_up_choice === null;
    }

    /*
    |--------------------------------------------------------------------------
    | CEK APAKAH GOOGLE FORM BOLEH DIAKSES
    |--------------------------------------------------------------------------
    */

    public function canProceedToInternshipForm(): bool
    {
        $letter = $this->latestDocument(
            ParticipantApplicationDocument::TYPE_REQUEST_LETTER
        );

        /*
        |--------------------------------------------------------------------------
        | Surat belum disetujui
        |--------------------------------------------------------------------------
        */

        if (
            $letter?->review_status
            !== ParticipantApplicationDocument::REVIEW_APPROVED
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Surat normal / mencantumkan permintaan sertifikat
        |--------------------------------------------------------------------------
        |
        | Jika certificate_eligible bukan false, maka surat dianggap normal.
        |
        */

        if ($letter->certificate_eligible !== false) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Surat disetujui tanpa pernyataan sertifikat
        |--------------------------------------------------------------------------
        |
        | Hanya pilihan "continue_without_upload" yang membuka Google Form.
        |
        | upload_again TIDAK membuka Google Form.
        |
        */

        return $this->certificate_follow_up_choice
            === 'continue_without_upload';
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS PENGAJUAN
    |--------------------------------------------------------------------------
    */

    public function isClosed(): bool
    {
        if (
            in_array(
                $this->status,
                ['rejected', 'completed'],
                true
            )
        ) {
            return true;
        }

        return $this->service_type
            === self::SERVICE_MAGANG_PKL
            && $this->official_ended_at
                ?->copy()
                ->endOfDay()
                ->isPast();
    }

    /**
     * @return array<string, array{
     *     label: string,
     *     description: string
     * }>
     */
    public static function serviceOptions(): array
    {
        return [
            self::SERVICE_MAGANG_PKL => [
                'label' => 'Kerja Praktik, Magang, atau PKL',
                'description' =>
                    'Persiapan administrasi untuk kegiatan magang atau praktik kerja lapangan.',
            ],

            self::SERVICE_WOPPS => [
                'label' => 'WOPPS',
                'description' =>
                    'Wawancara, observasi, penelitian, pendataan, survei, dan layanan terkait.',
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
            self::SERVICE_WOPPS =>
                'https://bit.ly/WOPPS',

            default =>
                'https://tinyurl.com/DaftarMagangDKP-PT',
        };
    }

    /**
     * @return array<string, string>
     */
    public function googleFormOptions(): array
    {
        return match ($this->service_type) {
            self::SERVICE_WOPPS => [
                'Buka Google Form WOPPS' =>
                    'https://bit.ly/WOPPS',
            ],

            default => [
                'SMA/SMK' =>
                    'https://tinyurl.com/DaftarMagangDKP-SM',

                'Perguruan Tinggi' =>
                    'https://tinyurl.com/DaftarMagangDKP-PT',
            ],
        };
    }

    public function getApplicationCodeAttribute(): string
    {
        return 'KP-'
            . now()->format('Y')
            . '-'
            . str_pad(
                (string) $this->id,
                5,
                '0',
                STR_PAD_LEFT
            );
    }

    /**
     * @return list<array{
     *     label: string,
     *     description: string
     * }>
     */
    public function preparationChecklist(): array
    {
        return match ($this->service_type) {
            self::SERVICE_WOPPS => [
                [
                    'label' => 'Identitas diri yang masih berlaku',
                    'description' => 'KTM, KTP, atau SIM.',
                ],
                [
                    'label' => 'Surat permohonan resmi',
                    'description' =>
                        'Dari institusi pendidikan atau instansi asal.',
                ],
                [
                    'label' => 'Proposal kegiatan',
                    'description' =>
                        'Sesuai kebutuhan layanan.',
                ],
                [
                    'label' => 'Persetujuan etik',
                    'description' =>
                        'Bila dipersyaratkan untuk kegiatan.',
                ],
            ],

            default => [
                [
                    'label' => 'Buku Tamu Magang / PKL',
                    'description' =>
                        'Diisi secara individu sebagai pendataan awal.',
                ],
                [
                    'label' => 'Koordinasi kuota',
                    'description' =>
                        'Konfirmasi ketersediaan kuota dan kesesuaian jurusan.',
                ],
                [
                    'label' => 'Surat permohonan resmi',
                    'description' =>
                        'Dari sekolah atau perguruan tinggi, dengan informasi lengkap.',
                ],
                [
                    'label' => 'Kebutuhan sertifikat',
                    'description' =>
                        'Dicantumkan sejak awal bila diperlukan.',
                ],
            ],
        };
    }
}