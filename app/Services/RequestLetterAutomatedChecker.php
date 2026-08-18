<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Throwable;

final class RequestLetterAutomatedChecker
{
    /**
     * @return array{status: string, summary: string, checks: list<array{key: string, label: string, status: string, message: string}>}
     */
    public function check(string $filePath, ?string $expectedParticipantName = null): array
    {
        $text = $this->extractText(Storage::disk('local')->path($filePath));

        return $this->checkText($text, $expectedParticipantName);
    }

    /**
     * Memeriksa teks hasil ekstraksi PDF. Dipisahkan dari pembacaan berkas agar
     * aturan pemeriksaan dapat diuji tanpa bergantung pada bentuk internal PDF.
     *
     * @return array{status: string, summary: string, checks: list<array{key: string, label: string, status: string, message: string}>}
     */
    public function checkText(string $text, ?string $expectedParticipantName = null): array
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
        $checks = [
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
        preg_match_all(
            '/^\s*nama(?:\s+(?:mahasiswa|siswa|peserta))?\s*[:\-]\s*([^\r\n]+)/imu',
            $text,
            $matches
        );

        $names = collect($matches[1] ?? [])
            ->map(fn (string $name): string => trim(preg_replace('/\s+/u', ' ', $name) ?? $name))
            ->filter(fn (string $name): bool => $name !== '')
            ->values();

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
        $hasNotes = str_contains($normalized, 'catatan untuk pengisi surat') || str_contains($normalized, 'hapus bagian ini sebelum surat');

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
