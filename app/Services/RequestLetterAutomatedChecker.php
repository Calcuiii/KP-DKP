<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ParticipantApplication;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Throwable;

final class RequestLetterAutomatedChecker
{
    /**
     * @return array{status: string, summary: string, checks: list<array{key: string, label: string, status: string, message: string}>}
     */
    public function check(string $filePath, ?string $expectedParticipantName = null, ?string $serviceType = null): array
    {
        $text = $this->extractText(Storage::disk('local')->path($filePath));

        return $this->checkText($text, $expectedParticipantName, $serviceType);
    }

    /**
     * Memeriksa teks hasil ekstraksi PDF. Dipisahkan dari pembacaan berkas agar
     * aturan pemeriksaan dapat diuji tanpa bergantung pada bentuk internal PDF.
     *
     * @return array{status: string, summary: string, checks: list<array{key: string, label: string, status: string, message: string}>}
     */
    public function checkText(string $text, ?string $expectedParticipantName = null, ?string $serviceType = null): array
    {

        if (mb_strlen(trim($text)) < 80) {
            return [
                'status' => 'unreadable',
                'summary' => 'Teks PDF tidak dapat dibaca. Unggah PDF digital yang teksnya dapat diseleksi.',
                'checks' => [[
                    'key' => 'readable_pdf', 'label' => 'PDF dapat dibaca', 'status' => 'failed',
                    'message' => 'PDF kemungkinan berupa scan/gambar atau tidak memiliki lapisan teks.',
                ]],
            ];
        }

        $normalized = $this->normalize($text);
        $checks = $serviceType === ParticipantApplication::SERVICE_WOPPS
            ? $this->woppsChecks($text, $normalized, $expectedParticipantName)
            : [
            $this->containsCheck('letterhead', 'Kop surat institusi', $normalized, ['kop surat resmi', 'universitas', 'sekolah', 'institut', 'politeknik'], 'Kop atau nama institusi pendidikan belum terdeteksi.'),
            $this->containsCheck('recipient', 'Tujuan surat', $normalized, ['kepala dinas kelautan dan perikanan provinsi jawa timur'], 'Surat harus ditujukan kepada Kepala Dinas Kelautan dan Perikanan Provinsi Jawa Timur.'),
            $this->subjectCheck($normalized),
            $this->participantIdentityCheck($text, $expectedParticipantName),
            $this->containsCheck('student_number', 'NIM atau NIS', $normalized, ['nim', 'nis', 'nisn'], 'NIM/NIS peserta belum ditemukan.'),
            $this->containsCheck('study_program', 'Program studi atau jurusan', $normalized, ['program studi', 'jurusan', 'kompetensi keahlian'], 'Program studi atau jurusan belum ditemukan.'),
            $this->containsCheck('location', 'Lokasi kegiatan', $normalized, ['lokasi kegiatan', 'upt ', 'instalasi ', 'cabang dinas'], 'Lokasi UPT/Instalasi/Cabang Dinas belum dicantumkan.'),
            $this->periodCheck($text, $normalized),
            $this->contactCheck($text, $normalized),
            $this->placeholderCheck($text, $normalized),
            $this->notesPageCheck($normalized),
            $this->spellingCheck($normalized),
            [
                'key' => 'signature', 'label' => 'Tanda tangan dan stempel', 'status' => 'manual',
                'message' => 'Keaslian tanda tangan, TTE, dan stempel harus diperiksa admin.',
            ],
            ];

        $failed = count(array_filter($checks, fn (array $check): bool => $check['status'] === 'failed'));

        return [
            'status' => $failed > 0 ? 'needs_revision' : 'passed',
            'summary' => $failed > 0
                ? "Ditemukan {$failed} bagian yang perlu diperbaiki sebelum pemeriksaan admin."
                : 'Pemeriksaan awal lengkap. Surat tetap menunggu verifikasi final admin.',
            'checks' => $checks,
        ];
    }

    /** @return list<array{key: string, label: string, status: string, message: string}> */
    private function woppsChecks(string $text, string $normalized, ?string $expectedParticipantName): array
    {
        return [
            $this->containsCheck('letterhead', 'Surat resmi institusi', $normalized, ['universitas', 'institut', 'politeknik', 'sekolah tinggi', 'akademi'], 'Nama atau kop institusi pendidikan asal belum ditemukan.'),
            $this->woppsRecipientCheck($text),
            $this->participantIdentityCheck($text, $expectedParticipantName),
            $this->labeledValueCheck('student_number', 'Nomor Induk Mahasiswa', $text, ['nomor induk mahasiswa', 'nim'], 'Nomor Induk Mahasiswa (NIM) belum ditemukan.'),
            $this->labeledValueCheck('semester', 'Semester', $text, ['semester'], 'Semester mahasiswa belum dicantumkan.'),
            $this->labeledValueCheck('study_program', 'Program Studi / Departemen', $text, ['program studi/departemen', 'program studi', 'departemen'], 'Program Studi atau Departemen belum ditemukan.'),
            $this->labeledValueCheck('faculty', 'Fakultas', $text, ['fakultas'], 'Fakultas mahasiswa belum dicantumkan.'),
            $this->labeledValueCheck('university', 'Universitas', $text, ['universitas', 'institut', 'politeknik', 'sekolah tinggi', 'akademi'], 'Universitas atau perguruan tinggi asal belum dicantumkan.'),
            $this->supervisorContactCheck($text, $normalized),
            $this->dataDeadlineCheck($text, $normalized),
            $this->dataPurposeCheck($text),
            $this->placeholderCheck($text, $normalized),
            $this->notesPageCheck($normalized),
            $this->spellingCheck($normalized),
            [
                'key' => 'signature', 'label' => 'Tanda tangan dan stempel', 'status' => 'manual',
                'message' => 'Keaslian tanda tangan, TTE, dan stempel harus diperiksa admin.',
            ],
        ];
    }

    /** @param list<string> $labels */
    private function labeledValueCheck(string $key, string $label, string $text, array $labels, string $failure): array
    {
        $value = $this->extractLabeledValue($text, $labels);
        $passed = $value !== null && mb_strlen($value) >= 1;

        return ['key' => $key, 'label' => $label, 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Informasi ditemukan dalam surat.' : $failure];
    }

    /** @param list<string> $labels */
    private function extractLabeledValue(string $text, array $labels): ?string
    {
        $labelPattern = implode('|', array_map(fn (string $label): string => preg_quote($label, '/'), $labels));
        $lines = preg_split('/\R/u', $text) ?: [];

        foreach ($lines as $index => $line) {
            if (preg_match('/^\s*(?:'.$labelPattern.')(?:\s*[:\-]\s*|\s+)(.*?)\s*$/iu', $line, $match) !== 1) {
                continue;
            }

            $value = trim($match[1] ?? '');
            if ($value !== '' && preg_match('/^[.…\s]+$/u', $value) !== 1) {
                return $value;
            }

            for ($offset = 1; $offset <= 3; $offset++) {
                $nextLine = trim($lines[$index + $offset] ?? '');
                if ($nextLine !== '' && preg_match('/^[.…\s]+$/u', $nextLine) !== 1) {
                    return $nextLine;
                }
            }
        }

        return null;
    }

    private function woppsRecipientCheck(string $text): array
    {
        preg_match('/\byth\.?\s*(.*?)(?:\bdengan hormat\b|\bsehubungan\b)/isu', $text, $match);
        $recipientBlock = $this->normalize($match[1] ?? '');
        $passed = str_contains($recipientBlock, 'kepala dinas kelautan dan perikanan provinsi jawa timur');

        return ['key' => 'recipient', 'label' => 'Tujuan surat', 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Tujuan surat sesuai dengan nama instansi.' : 'Surat harus ditujukan kepada Kepala Dinas Kelautan dan Perikanan Provinsi Jawa Timur.'];
    }

    private function dataPurposeCheck(string $text): array
    {
        $value = $this->extractLabeledValue($text, ['tujuan penggunaan data/informasi', 'tujuan penggunaan data', 'tujuan penggunaan informasi', 'tujuan keperluan data']);
        $normalizedValue = $value === null ? '' : $this->normalize($value);
        $genericValues = ['untuk penelitian', 'untuk keperluan penelitian', 'keperluan penelitian', 'untuk tugas akhir'];
        $passed = mb_strlen($normalizedValue) >= 30 && ! in_array(rtrim($normalizedValue, '.'), $genericValues, true);

        return ['key' => 'data_purpose', 'label' => 'Tujuan Penggunaan Data / Informasi', 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Tujuan penggunaan data dijelaskan secara memadai.' : 'Jelaskan tujuan penggunaan data secara spesifik, bukan hanya “untuk penelitian”.'];
    }

    private function supervisorContactCheck(string $text, string $normalized): array
    {
        $hasSupervisor = str_contains($normalized, 'dosen pembimbing') || str_contains($normalized, 'dosen lapangan');
        $hasWhatsapp = str_contains($normalized, 'whatsapp') || str_contains($normalized, 'no wa') || str_contains($normalized, 'nomor wa');
        $hasNumber = preg_match('/(?:\+62|62|0)8[1-9][0-9\s().-]{7,15}/', $text) === 1;
        $passed = $hasSupervisor && $hasWhatsapp && $hasNumber;

        return ['key' => 'supervisor_contact', 'label' => 'Dosen Pembimbing dan WhatsApp', 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Nama dosen dan nomor WhatsApp aktif ditemukan.' : 'Cantumkan nama Dosen Pembimbing/Dosen Lapangan beserta nomor WhatsApp aktif.'];
    }

    private function dataDeadlineCheck(string $text, string $normalized): array
    {
        $value = $this->extractLabeledValue($text, [
            'batas waktu/deadline keperluan data',
            'batas waktu / deadline keperluan data',
            'batas waktu keperluan data',
            'deadline keperluan data',
            'batas waktu',
            'deadline',
        ]);
        $datePattern = '\\b(?:0?[1-9]|[12]\\d|3[01])(?:[\\/.-](?:0?[1-9]|1[0-2])[\\/.-]\\d{4}|\\s+(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\\s+\\d{4})\\b';
        $passed = $value !== null && preg_match('/'.$datePattern.'/iu', $value) === 1;

        // PDF bertata letak kolom dapat memecah label menjadi beberapa baris,
        // misalnya "Batas Waktu/Deadline Keperluan", "Data", lalu tanggal.
        if (! $passed) {
            $compactText = preg_replace('/\\s+/u', ' ', mb_strtolower($text)) ?? mb_strtolower($text);
            $deadlineLabel = '(?:batas\\s+waktu\\s*(?:\\/\\s*deadline)?\\s+keperluan\\s+data|deadline\\s+keperluan\\s+data)';
            $passed = preg_match('/'.$deadlineLabel.'[^0-9]{0,40}'.$datePattern.'/iu', $compactText) === 1;
        }

        return ['key' => 'data_deadline', 'label' => 'Batas Waktu Keperluan Data', 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Batas waktu keperluan data ditemukan.' : 'Cantumkan batas waktu atau deadline keperluan data dengan tanggal lengkap.'];
    }

    private function extractText(string $absolutePath): string
    {
        try {
            return (new Parser)->parseFile($absolutePath)->getText();
        } catch (Throwable) {
            return '';
        }
    }

    private function subjectCheck(string $normalized): array
    {
        $hasRequest = str_contains($normalized, 'permohonan');
        $hasActivity = collect(['magang', 'pkl', 'kerja praktik', 'kerja praktek'])
            ->contains(fn (string $term): bool => str_contains($normalized, $term));
        $passed = $hasRequest && $hasActivity;

        return [
            'key' => 'subject',
            'label' => 'Perihal permohonan',
            'status' => $passed ? 'passed' : 'failed',
            'message' => $passed ? 'Maksud permohonan Magang/PKL ditemukan.' : 'Perihal atau maksud permohonan Magang/PKL belum jelas.',
        ];
    }

    private function participantIdentityCheck(string $text, ?string $expectedParticipantName): array
    {
        $name = $this->extractLabeledValue($text, ['nama mahasiswa', 'nama siswa', 'nama peserta', 'nama']);
        $names = collect($name === null ? [] : [$name]);

        if ($names->isEmpty()) {
            return [
                'key' => 'participant_identity',
                'label' => 'Identitas peserta',
                'status' => 'failed',
                'message' => 'Bagian nama peserta belum ditemukan. Gunakan label seperti “Nama:”.',
            ];
        }

        if ($expectedParticipantName === null || trim($expectedParticipantName) === '') {
            return [
                'key' => 'participant_identity',
                'label' => 'Identitas peserta',
                'status' => 'passed',
                'message' => 'Nama peserta ditemukan dalam surat.',
            ];
        }

        $expected = $this->normalizeName($expectedParticipantName);
        $matchesAccount = $names->contains(
            fn (string $name): bool => $this->normalizeName($name) === $expected
        );

        return [
            'key' => 'participant_identity',
            'label' => 'Identitas peserta',
            'status' => $matchesAccount ? 'passed' : 'failed',
            'message' => $matchesAccount
                ? 'Nama peserta sesuai dengan nama akun terdaftar.'
                : 'Nama pada surat tidak sesuai dengan nama akun peserta. Periksa kembali ejaan nama lengkap.',
        ];
    }

    /** @param list<string> $needles */
    private function containsCheck(string $key, string $label, string $text, array $needles, string $failure): array
    {
        $found = count(array_filter($needles, fn (string $needle): bool => str_contains($text, $needle))) > 0;

        return ['key' => $key, 'label' => $label, 'status' => $found ? 'passed' : 'failed', 'message' => $found ? 'Bagian ditemukan dalam surat.' : $failure];
    }

    private function periodCheck(string $text, string $normalized): array
    {
        $labels = [
            'periode kegiatan',
            'tanggal pelaksanaan',
            'waktu pelaksanaan',
            'masa pelaksanaan',
            'jangka waktu',
        ];
        $hasLabel = collect($labels)->contains(fn (string $label): bool => str_contains($normalized, $label));
        $periodText = '';

        if ($hasLabel) {
            $labelPattern = implode('|', array_map(fn (string $label): string => preg_quote($label, '/'), $labels));

            if (preg_match('/(?:'.$labelPattern.')[^\r\n]*(?:\R[^\r\n]*)?/iu', $text, $periodMatch) === 1) {
                $periodText = $periodMatch[0];
            }
        }

        $dateMatches = preg_match_all('/\b(?:0?[1-9]|[12]\d|3[01])(?:[\/.-](?:0?[1-9]|1[0-2])[\/.-]\d{4}|\s+(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+\d{4})\b/iu', $periodText);
        $passed = $hasLabel && $dateMatches >= 2;

        return ['key' => 'period', 'label' => 'Periode kegiatan', 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Tanggal mulai dan selesai ditemukan.' : 'Cantumkan tanggal mulai dan selesai secara lengkap.'];
    }

    private function contactCheck(string $text, string $normalized): array
    {
        $hasLabel = str_contains($normalized, 'whatsapp') || str_contains($normalized, 'narahubung');
        $hasNumber = preg_match('/(?:\+62|62|0)8[1-9][0-9\s().-]{7,15}/', $text) === 1;
        $passed = $hasLabel && $hasNumber;

        return ['key' => 'contact', 'label' => 'Narahubung WhatsApp', 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Nomor WhatsApp ditemukan.' : 'Cantumkan nama dan nomor WhatsApp aktif perwakilan peserta.'];
    }

    private function placeholderCheck(string $text, string $normalized): array
    {
        $hasPlaceholder = str_contains($normalized, 'sebutkan kegiatan utama')
            || preg_match('/[…]{3,}|\.{8,}/u', $text) === 1;

        return ['key' => 'placeholders', 'label' => 'Placeholder template', 'status' => $hasPlaceholder ? 'failed' : 'passed', 'message' => $hasPlaceholder ? 'Masih terdapat placeholder atau bagian bertitik yang belum diisi.' : 'Tidak ditemukan placeholder kosong.'];
    }

    private function notesPageCheck(string $normalized): array
    {
        $hasNotes = str_contains($normalized, 'catatan untuk pengisi surat')
            || str_contains($normalized, 'hapus bagian ini sebelum surat')
            || str_contains($normalized, 'hapus bagian yang tidak sesuai')
            || str_contains($normalized, 'hapus yg tidak sesuai');

        return ['key' => 'template_notes', 'label' => 'Catatan template dihapus', 'status' => $hasNotes ? 'failed' : 'passed', 'message' => $hasNotes ? 'Hapus halaman atau bagian catatan pengisian sebelum mengirim surat.' : 'Catatan pengisian tidak ditemukan.'];
    }

    private function spellingCheck(string $normalized): array
    {
        $misspellings = [
            'permohoan' => 'permohonan',
            'pelaksanan' => 'pelaksanaan',
        ];

        foreach ($misspellings as $incorrect => $correct) {
            if (preg_match('/\b'.preg_quote($incorrect, '/').'\b/u', $normalized) === 1) {
                return [
                    'key' => 'spelling',
                    'label' => 'Ejaan istilah penting',
                    'status' => 'failed',
                    'message' => "Ditemukan kemungkinan salah eja “{$incorrect}”. Gunakan “{$correct}”.",
                ];
            }
        }

        return [
            'key' => 'spelling',
            'label' => 'Ejaan istilah penting',
            'status' => 'passed',
            'message' => 'Tidak ditemukan salah eja umum pada istilah penting.',
        ];
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtolower($name);
        $name = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $name) ?? $name;

        return preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s+.-]+/u', ' ', $text) ?? $text;

        return preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
    }
}
