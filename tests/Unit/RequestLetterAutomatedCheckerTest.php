<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ParticipantApplication;
use App\Services\RequestLetterAutomatedChecker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RequestLetterAutomatedCheckerTest extends TestCase
{
    #[Test]
    public function it_recognizes_a_plain_name_label_and_waktu_pelaksanaan(): void
    {
        $result = app(RequestLetterAutomatedChecker::class)->checkText(
            $this->validLetterText(),
            'Andi Pratomo'
        );

        $checks = collect($result['checks'])->keyBy('key');

        $this->assertSame('passed', $result['status']);
        $this->assertSame('passed', $checks['participant_identity']['status']);
        $this->assertSame('passed', $checks['period']['status']);
    }

    #[Test]
    public function it_reports_a_name_mismatch_and_a_known_misspelling(): void
    {
        $text = str_replace(
            'permohonan pelaksanaan',
            'permohoan pelaksanaan',
            $this->validLetterText()
        );

        $result = app(RequestLetterAutomatedChecker::class)->checkText($text, 'Andi Pratama');
        $checks = collect($result['checks'])->keyBy('key');

        $this->assertSame('needs_revision', $result['status']);
        $this->assertSame('failed', $checks['participant_identity']['status']);
        $this->assertStringContainsString('tidak sesuai', $checks['participant_identity']['message']);
        $this->assertSame('failed', $checks['spelling']['status']);
        $this->assertStringContainsString('permohoan', $checks['spelling']['message']);
    }

    #[Test]
    public function it_does_not_count_an_unrelated_letter_date_as_the_end_date(): void
    {
        $text = str_replace(
            '1 September 2026 sampai 30 November 2026',
            'mulai tanggal 1 September 2026',
            $this->validLetterText()
        );
        $text = "Surabaya, 10 Agustus 2026\n".$text;

        $result = app(RequestLetterAutomatedChecker::class)->checkText($text, 'Andi Pratomo');
        $checks = collect($result['checks'])->keyBy('key');

        $this->assertSame('failed', $checks['period']['status']);
        $this->assertStringContainsString('mulai dan selesai', $checks['period']['message']);
    }

    #[Test]
    public function it_recognizes_all_required_wopps_information(): void
    {
        $result = app(RequestLetterAutomatedChecker::class)->checkText(
            $this->validWoppsLetterText(),
            'Andi Pratomo',
            ParticipantApplication::SERVICE_WOPPS
        );
        $checks = collect($result['checks'])->keyBy('key');

        $this->assertSame('passed', $result['status']);
        foreach (['participant_identity', 'student_number', 'semester', 'study_program', 'faculty', 'university', 'supervisor_contact', 'data_deadline', 'data_purpose'] as $key) {
            $this->assertSame('passed', $checks[$key]['status'], "WOPPS check {$key} should pass.");
        }
    }

    #[Test]
    public function it_reports_missing_wopps_supervisor_contact_and_deadline(): void
    {
        $text = str_replace([
            "Dosen Pembimbing dan WhatsApp : Dr. Siti Aminah, 081234567890\n",
            "Batas Waktu Keperluan Data : 30 September 2026\n",
        ], '', $this->validWoppsLetterText());

        $result = app(RequestLetterAutomatedChecker::class)->checkText($text, 'Andi Pratomo', ParticipantApplication::SERVICE_WOPPS);
        $checks = collect($result['checks'])->keyBy('key');

        $this->assertSame('needs_revision', $result['status']);
        $this->assertSame('failed', $checks['supervisor_contact']['status']);
        $this->assertSame('failed', $checks['data_deadline']['status']);
    }

    #[Test]
    public function it_recognizes_a_combined_wopps_deadline_label(): void
    {
        $text = str_replace(
            'Batas Waktu Keperluan Data : 30 September 2026',
            'Batas Waktu/Deadline Keperluan Data 30 September 2026',
            $this->validWoppsLetterText()
        );

        $result = app(RequestLetterAutomatedChecker::class)->checkText(
            $text,
            'Andi Pratomo',
            ParticipantApplication::SERVICE_WOPPS
        );
        $checks = collect($result['checks'])->keyBy('key');

        $this->assertSame('passed', $checks['data_deadline']['status']);
    }

    #[Test]
    public function it_recognizes_a_wopps_deadline_label_split_by_pdf_extraction(): void
    {
        $text = str_replace(
            'Batas Waktu Keperluan Data : 30 September 2026',
            "Batas Waktu/Deadline Keperluan \nData\n30 September 2026",
            $this->validWoppsLetterText()
        );

        $result = app(RequestLetterAutomatedChecker::class)->checkText(
            $text,
            'Andi Pratomo',
            ParticipantApplication::SERVICE_WOPPS
        );
        $checks = collect($result['checks'])->keyBy('key');

        $this->assertSame('passed', $checks['data_deadline']['status']);
    }

    #[Test]
    public function it_reads_column_aligned_wopps_fields_without_colons(): void
    {
        $text = <<<'TEXT'
UNIVERSITAS TEKNOLOGI NUSANTARA
Perihal     Permohonan Penelitian/Permintaan Data (hapus bagian yang tidak sesuai)
Yth.
Kepala Dinas Perikanan Jawa Timur
Nama Mahasiswa          Peserta Testing Si-Molek
Nomor Induk Mahasiswa   2205123456
Program Studi/Departemen Sistem Informasi
Fakultas                Fakultas Ilmu Komputer
Universitas             Universitas Teknologi Nusantara
Dosen Pembimbing        Dr. Siti Rahmawati, M.Kom.
Keterangan Tambahan     ................................
Tujuan Penggunaan Data/Informasi:

Untuk keperluan penelitian.
Surabaya, 24 Agustus 2026
TEXT;

        $result = app(RequestLetterAutomatedChecker::class)->checkText($text, 'Peserta Testing SI-Molek', ParticipantApplication::SERVICE_WOPPS);
        $checks = collect($result['checks'])->keyBy('key');

        foreach (['participant_identity', 'student_number', 'study_program', 'faculty', 'university'] as $key) {
            $this->assertSame('passed', $checks[$key]['status'], "Column-aligned field {$key} should pass.");
        }
        foreach (['recipient', 'semester', 'supervisor_contact', 'data_deadline', 'data_purpose', 'placeholders', 'template_notes'] as $key) {
            $this->assertSame('failed', $checks[$key]['status'], "Deliberate error {$key} should fail.");
        }
    }

    private function validLetterText(): string
    {
        return <<<'TEXT'
UNIVERSITAS TEKNOLOGI NUSANTARA
Nomor: 123/UTN/VIII/2026
Perihal: Permohonan Magang

Yth. Kepala Dinas Kelautan dan Perikanan Provinsi Jawa Timur
Dengan hormat,

Dengan hormat, kami mengajukan permohonan pelaksanaan magang bagi:
Nama : Andi Pratomo
NIM : 22000123
Program Studi : Teknik Informatika
Lokasi Kegiatan : UPT Pengujian Mutu Surabaya
Waktu Pelaksanaan : 1 September 2026 sampai 30 November 2026
Narahubung WhatsApp : Andi, 081231987217

Demikian surat ini disampaikan. Atas perhatian Bapak/Ibu kami ucapkan terima kasih.
TEXT;
    }

    private function validWoppsLetterText(): string
    {
        return <<<'TEXT'
UNIVERSITAS TEKNOLOGI NUSANTARA
Nomor: 124/UTN/VIII/2026
Perihal: Permohonan Data Penelitian
Yth. Kepala Dinas Kelautan dan Perikanan Provinsi Jawa Timur

Dengan hormat, kami mengajukan permohonan kebutuhan data bagi:
Nama Mahasiswa : Andi Pratomo
Nomor Induk Mahasiswa : 22000123
Semester : 7
Program Studi / Departemen : Teknik Informatika
Fakultas : Teknik
Universitas : Universitas Teknologi Nusantara
Dosen Pembimbing dan WhatsApp : Dr. Siti Aminah, 081234567890
Batas Waktu Keperluan Data : 30 September 2026
Tujuan Penggunaan Data : Penyusunan tugas akhir mengenai sistem informasi kelautan.

Demikian surat ini disampaikan. Atas perhatian Bapak/Ibu kami ucapkan terima kasih.
TEXT;
    }
}
