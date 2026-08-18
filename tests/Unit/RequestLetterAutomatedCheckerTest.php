<?php

declare(strict_types=1);

namespace Tests\Unit;

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

    private function validLetterText(): string
    {
        return <<<'TEXT'
UNIVERSITAS TEKNOLOGI NUSANTARA
Nomor: 123/UTN/VIII/2026
Perihal: Permohonan Magang

Yth. Kepala Dinas Kelautan dan Perikanan Provinsi Jawa Timur

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
}
