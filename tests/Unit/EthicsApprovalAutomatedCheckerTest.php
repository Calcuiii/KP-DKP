<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\EthicsApprovalAutomatedChecker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EthicsApprovalAutomatedCheckerTest extends TestCase
{
    #[Test]
    public function it_accepts_a_completed_ethics_approval_template(): void
    {
        $result = app(EthicsApprovalAutomatedChecker::class)->checkText($this->completedTemplate(), 'Andi Pratomo');
        $checks = collect($result['checks'])->keyBy('key');

        $this->assertSame('passed', $result['status']);
        $this->assertSame('passed', $checks['participant_name']['status']);
        $this->assertSame('passed', $checks['period']['status']);
        $this->assertSame('passed', $checks['ethics_declarations']['status']);
        $this->assertSame('passed', $checks['template_cleanup']['status']);
    }

    #[Test]
    public function it_rejects_an_unfilled_template_and_missing_declaration(): void
    {
        $text = str_replace(
            ['Nama Lengkap : Andi Pratomo', 'Tidak akan menyebarluaskan Data kegiatan melalui media sosial apapun.'],
            ['Nama Lengkap : ................................', ''],
            $this->completedTemplate()
        );
        $result = app(EthicsApprovalAutomatedChecker::class)->checkText($text, 'Andi Pratomo');
        $checks = collect($result['checks'])->keyBy('key');

        $this->assertSame('needs_revision', $result['status']);
        $this->assertSame('failed', $checks['participant_name']['status']);
        $this->assertSame('failed', $checks['ethics_declarations']['status']);
        $this->assertSame('failed', $checks['template_cleanup']['status']);
    }

    private function completedTemplate(): string
    {
        return <<<'TEXT'
SURAT PERNYATAAN PERSETUJUAN ETIKA
(ETHICS APPROVAL STATEMENT LETTER)
Kepada Yth Bapak Kepala Dinas Kelautan dan Perikanan Provinsi Jawa Timur
Nama Lengkap : Andi Pratomo
Nomor Induk Mahasiswa : 22000123
Program Studi : Teknik Informatika
Fakultas : Teknik
Institusi : Universitas Teknologi Nusantara
Jenis Kegiatan : Penelitian
Periode Kegiatan : 1 September 2026 sampai 30 November 2026
Lokasi Kegiatan : Dinas Kelautan dan Perikanan Provinsi Jawa Timur
Judul / Tema Laporan : Analisis sistem informasi data kelautan
Saya bersedia merahasiakan identitas pribadi Narasumber dan Kepegawaian.
Saya bersedia merahasiakan data sensitif seperti Data Keuangan.
Tidak akan menyebarluaskan Data kegiatan melalui media sosial apapun.
Data hanya akan digunakan untuk keperluan akademik.
Surabaya, 20 Agustus 2026
Yang membuat pernyataan, Andi Pratomo
Mengetahui, Dosen Pembimbing Dr. Siti Aminah, NIP 198001012010012001
Materai Tempel Rp 10.000 dan Stempel Basah Asli Institusi Pendidikan
TEXT;
    }
}
