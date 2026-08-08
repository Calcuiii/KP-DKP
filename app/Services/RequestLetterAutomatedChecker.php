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
    public function check(string $filePath): array
    {
        $text = $this->extractText(Storage::disk('local')->path($filePath));

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
            $this->containsCheck('subject', 'Perihal permohonan', $normalized, ['permohonan', 'magang', 'pkl', 'kerja praktik', 'kerja praktek'], 'Perihal atau maksud permohonan Magang/PKL belum jelas.'),
            $this->containsCheck('participant_identity', 'Identitas peserta', $normalized, ['nama mahasiswa', 'nama siswa', 'nama peserta'], 'Bagian nama peserta belum ditemukan.'),
            $this->containsCheck('student_number', 'NIM atau NIS', $normalized, ['nim', 'nis', 'nisn'], 'NIM/NIS peserta belum ditemukan.'),
            $this->containsCheck('study_program', 'Program studi atau jurusan', $normalized, ['program studi', 'jurusan', 'kompetensi keahlian'], 'Program studi atau jurusan belum ditemukan.'),
            $this->containsCheck('location', 'Lokasi kegiatan', $normalized, ['lokasi kegiatan', 'upt ', 'instalasi ', 'cabang dinas'], 'Lokasi UPT/Instalasi/Cabang Dinas belum dicantumkan.'),
            $this->periodCheck($text, $normalized),
            $this->contactCheck($text, $normalized),
            $this->placeholderCheck($text, $normalized),
            $this->notesPageCheck($normalized),
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
            return (new Parser())->parseFile($absolutePath)->getText();
        } catch (Throwable) {
            return '';
        }
    }

    /** @param list<string> $needles */
    private function containsCheck(string $key, string $label, string $text, array $needles, string $failure): array
    {
        $found = count(array_filter($needles, fn (string $needle): bool => str_contains($text, $needle))) > 0;

        return ['key' => $key, 'label' => $label, 'status' => $found ? 'passed' : 'failed', 'message' => $found ? 'Bagian ditemukan dalam surat.' : $failure];
    }

    private function periodCheck(string $text, string $normalized): array
    {
        $hasLabel = str_contains($normalized, 'periode kegiatan') || str_contains($normalized, 'tanggal pelaksanaan');
        $dateMatches = preg_match_all('/\b(?:0?[1-9]|[12]\d|3[01])(?:[\/.-](?:0?[1-9]|1[0-2])[\/.-]\d{4}|\s+(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+\d{4})\b/iu', $text);
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

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s+.-]+/u', ' ', $text) ?? $text;

        return preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
    }
}
