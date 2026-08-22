<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Throwable;

final class EthicsApprovalAutomatedChecker
{
    public function check(string $filePath, ?string $expectedParticipantName = null): array
    {
        try {
            $text = (new Parser)->parseFile(Storage::disk('local')->path($filePath))->getText();
        } catch (Throwable) {
            $text = '';
        }

        return $this->checkText($text, $expectedParticipantName);
    }

    public function checkText(string $text, ?string $expectedParticipantName = null): array
    {
        if (mb_strlen(trim($text)) < 100) {
            return ['status' => 'unreadable', 'summary' => 'Teks PDF tidak dapat dibaca. Unggah PDF digital yang teksnya dapat diseleksi.', 'checks' => [[
                'key' => 'readable_pdf', 'label' => 'PDF dapat dibaca', 'status' => 'failed', 'message' => 'PDF kemungkinan berupa scan/gambar atau tidak memiliki lapisan teks.',
            ]]];
        }

        $normalized = $this->normalize($text);
        $checks = [
            $this->termsCheck('template_title', 'Judul dokumen resmi', $normalized, ['surat pernyataan persetujuan etika', 'ethics approval statement letter'], 'Judul Ethics Approval Statement Letter belum sesuai template.'),
            $this->termsCheck('recipient', 'Tujuan surat', $normalized, ['kepala dinas kelautan dan perikanan', 'provinsi jawa timur'], 'Tujuan surat kepada Kepala DKP Provinsi Jawa Timur belum ditemukan.', true),
            $this->participantNameCheck($text, $expectedParticipantName),
            $this->termsCheck('student_number', 'NIS / NIM', $normalized, ['nomer induk siswa', 'nomer induk mahasiswa', 'nomor induk siswa', 'nomor induk mahasiswa', 'nim'], 'NIS atau NIM belum diisi.'),
            $this->termsCheck('study_program', 'Kompetensi Keahlian / Program Studi', $normalized, ['kompetensi keahlian', 'program studi'], 'Kompetensi Keahlian atau Program Studi belum ditemukan.'),
            $this->termsCheck('faculty', 'Fakultas', $normalized, ['fakultas'], 'Fakultas belum dicantumkan.'),
            $this->termsCheck('institution', 'Sekolah / Institusi', $normalized, ['nama sekolah', 'institusi'], 'Nama sekolah atau institusi belum dicantumkan.'),
            $this->termsCheck('activity_type', 'Jenis kegiatan', $normalized, ['jenis kegiatan'], 'Jenis kegiatan belum dicantumkan.'),
            $this->periodCheck($text, $normalized),
            $this->labeledContentCheck('location', 'Lokasi kegiatan', $text, ['lokasi kegiatan'], 'Lokasi kegiatan belum diisi.'),
            $this->labeledContentCheck('report_title', 'Judul / Tema laporan', $text, ['judul / tema laporan', 'judul atau tema laporan'], 'Judul atau tema laporan belum diisi.'),
            $this->declarationCheck($normalized),
            $this->templateCleanupCheck($text, $normalized),
            ['key' => 'participant_signature', 'label' => 'Tanda tangan dan meterai peserta', 'status' => 'manual', 'message' => 'Tanda tangan peserta dan meterai Rp10.000 harus diverifikasi admin.'],
            ['key' => 'institution_approval', 'label' => 'Persetujuan pembimbing/dekan dan stempel', 'status' => 'manual', 'message' => 'Nama, tanda tangan pembimbing/dekan, NIP, dan stempel basah institusi harus diverifikasi admin.'],
        ];

        $failed = count(array_filter($checks, fn (array $check): bool => $check['status'] === 'failed'));

        return [
            'status' => $failed > 0 ? 'needs_revision' : 'passed',
            'summary' => $failed > 0 ? "Ditemukan {$failed} bagian yang perlu diperbaiki sebelum pemeriksaan admin." : 'Pemeriksaan awal lengkap. Dokumen tetap menunggu verifikasi final admin.',
            'checks' => $checks,
        ];
    }

    private function participantNameCheck(string $text, ?string $expectedName): array
    {
        preg_match('/nama lengkap\s*[:\-]\s*([^\r\n]+)/iu', $text, $match);
        $found = trim($match[1] ?? '');
        $passed = $found !== '' && ($expectedName === null || $this->normalizeName($found) === $this->normalizeName($expectedName));

        return ['key' => 'participant_name', 'label' => 'Nama lengkap peserta', 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Nama sesuai dengan akun peserta.' : 'Nama lengkap belum diisi atau tidak sesuai dengan akun peserta.'];
    }

    private function periodCheck(string $text, string $normalized): array
    {
        $hasLabel = str_contains($normalized, 'periode kegiatan');
        preg_match('/periode kegiatan[^\r\n]*(?:\R[^\r\n]*)?/iu', $text, $match);
        $dateCount = preg_match_all('/\b(?:0?[1-9]|[12]\d|3[01])(?:[\/.-](?:0?[1-9]|1[0-2])[\/.-]\d{4}|\s+(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+\d{4})\b/iu', $match[0] ?? '');
        $passed = $hasLabel && $dateCount >= 2;

        return ['key' => 'period', 'label' => 'Periode kegiatan', 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Tanggal mulai dan selesai ditemukan.' : 'Isi tanggal mulai dan selesai kegiatan secara lengkap.'];
    }

    private function declarationCheck(string $normalized): array
    {
        $phrases = ['merahasiakan identitas pribadi', 'merahasiakan data sensitif', 'tidak akan menyebarluaskan', 'hanya akan digunakan untuk keperluan akademik'];
        $passed = collect($phrases)->every(fn (string $phrase): bool => str_contains($normalized, $phrase));

        return ['key' => 'ethics_declarations', 'label' => 'Empat pernyataan etika', 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Seluruh pernyataan kerahasiaan dan penggunaan akademik ditemukan.' : 'Empat butir pernyataan etika harus dipertahankan sesuai template resmi.'];
    }

    private function templateCleanupCheck(string $text, string $normalized): array
    {
        $hasPlaceholder = preg_match('/[…]{3,}|\.{8,}|tgl\s*\.{3,}|bulan\s*\.{3,}|tahun\s*\.{3,}/iu', $text) === 1;
        $hasInstruction = str_contains($normalized, 'hapus yg tidak sesuai') || str_contains($normalized, 'hapus yang tidak sesuai') || str_contains($normalized, 'tuliskan');
        $passed = ! $hasPlaceholder && ! $hasInstruction;

        return ['key' => 'template_cleanup', 'label' => 'Template telah diisi dan dirapikan', 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Placeholder dan petunjuk template tidak ditemukan.' : 'Masih ada titik kosong, pilihan yang belum dihapus, atau petunjuk template.'];
    }

    private function labeledContentCheck(string $key, string $label, string $text, array $labels, string $failure): array
    {
        $pattern = implode('|', array_map(fn (string $value): string => preg_quote($value, '/'), $labels));
        $passed = preg_match('/(?:'.$pattern.')\s*[:\-]\s*[^\r\n]{3,}/iu', $text) === 1;

        return ['key' => $key, 'label' => $label, 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Informasi ditemukan dalam dokumen.' : $failure];
    }

    private function termsCheck(string $key, string $label, string $text, array $terms, string $failure, bool $requireAll = false): array
    {
        $passed = $requireAll
            ? collect($terms)->every(fn (string $term): bool => str_contains($text, $term))
            : collect($terms)->contains(fn (string $term): bool => str_contains($text, $term));

        return ['key' => $key, 'label' => $label, 'status' => $passed ? 'passed' : 'failed', 'message' => $passed ? 'Bagian ditemukan dalam dokumen.' : $failure];
    }

    private function normalizeName(string $name): string
    {
        return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', mb_strtolower($name)) ?? '') ?? '');
    }

    private function normalize(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}\s+.-]+/u', ' ', mb_strtolower($text)) ?? '') ?? '');
    }
}
