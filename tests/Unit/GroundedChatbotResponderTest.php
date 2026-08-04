<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseGroundedContextBuilder;
use App\Services\GroundedChatbotResponder;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GroundedChatbotResponderTest extends TestCase
{
    public function test_it_returns_a_bounded_grounded_answer_without_markdown_headings(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'Sebutkan kelengkapan surat permohonan magang.',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertStringStartsWith(
            "Berikut informasi yang tersedia pada dokumen resmi:\n",
            $result['answer'],
        );
        self::assertStringNotContainsString('## ', $result['answer']);
        self::assertStringContainsString('**', $result['answer']);
        self::assertStringContainsString(
            'Anda dapat membuka bagian sumber di bawah untuk melihat dokumen lengkap.',
            $result['answer'],
        );
        self::assertLessThanOrEqual(5500, $this->bodyLength($result['answer']));
    }

    public function test_it_returns_the_approved_structured_answer_for_each_magang_quick_question(): void
    {
        $answers = [
            'Apa saja persyaratan pengajuan magang?' => [
                'nama lengkap;',
                'https://bit.ly/Surat_Permohonan_DKP',
                'KB-004',
            ],
            'Bagaimana alur pengajuan Magang / PKL?' => [
                '1. Isi Buku Tamu Magang / PKL.',
                '11. Proses dokumen akhir dilakukan sesuai ketentuan yang berlaku.',
                'KB-007',
            ],
            'Bagaimana ketentuan peserta selama magang?' => [
                'Jumat pukul 07.00–16.30 WIB',
                'Pelanggaran dapat dikenai pembinaan',
                'KB-001',
            ],
            'Bagaimana penerbitan surat keterangan dan sertifikat?' => [
                'tidak lagi menerbitkan Surat Keterangan Magang, PKL, atau Penelitian.',
                'https://bit.ly/Surat_Permohonan_DKP',
                'KB-008',
            ],
            'Apa saja persyaratan pengajuan penelitian atau permintaan data?' => [
                'Form Ethical Clearance atau Persetujuan Etik',
                'https://bit.ly/EASL-DKP',
                'KB-009',
            ],
            'Bagaimana cara mengajukan wawancara atau observasi?' => [
                'Unggah dokumen pengajuan melalui formulir layanan WOPPS',
                'https://bit.ly/WOPPS',
                'KB-009',
            ],
            'Dokumen apa saja yang perlu disiapkan untuk WOPPS?' => [
                'Nama dosen pembimbing atau dosen lapangan',
                'Proposal kegiatan sesuai format institusi asal.',
                'KB-009',
            ],
            'Ke mana saya mengirimkan dokumen pengajuan?' => [
                'https://bit.ly/WOPPS',
                '0852-53000-485',
                'KB-009',
            ],
        ];

        foreach ($answers as $question => [$firstExpectedText, $secondExpectedText, $documentId]) {
            $result = app(GroundedChatbotResponder::class)->answer($question);

            self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
            self::assertStringContainsString($firstExpectedText, $result['answer']);
            self::assertStringContainsString($secondExpectedText, $result['answer']);
            self::assertSame($documentId, $result['sources'][0]['document_id']);
        }
    }

    public function test_it_keeps_the_insufficient_information_response_when_no_source_matches(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'kataunikyangtidakada123456789',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_INSUFFICIENT_INFORMATION, $result['status']);
        self::assertSame([], $result['sources']);
    }

    public function test_it_preserves_every_selected_section_without_mid_section_truncation(): void
    {
        $question = 'Sebutkan kelengkapan surat permohonan magang.';
        $context = app(KnowledgeBaseGroundedContextBuilder::class)->buildAll($question);
        $result = app(GroundedChatbotResponder::class)->answer($question);

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);

        foreach ($result['sources'] as $publicSource) {
            $source = $this->sourceFor($context->sources, $publicSource);

            self::assertIsArray($source);
            self::assertStringContainsString(
                $this->formatSection($source['content']),
                $result['answer'],
            );
        }
    }

    public function test_it_does_not_expose_chatbot_notes_for_an_ordered_magang_flow_query(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'Jelaskan alur dan langkah Magang secara berurutan.',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertStringNotContainsString(
            'Untuk pertanyaan tentang alur Magang atau PKL',
            $result['answer'],
        );
        self::assertStringNotContainsString(
            'Jangan menyatakan Surat Keterangan atau Sertifikat terbit otomatis',
            $result['answer'],
        );

        foreach ($result['sources'] as $source) {
            self::assertNotSame('Catatan untuk Jawaban Chatbot', $source['section_title']);
        }
    }

    public function test_it_presents_sections_from_the_same_document_in_section_order(): void
    {
        $question = 'Bagaimana alur utama dari pengajuan magang hingga selesai kegiatan magang?';
        $context = app(KnowledgeBaseGroundedContextBuilder::class)->buildAll($question);
        $result = app(GroundedChatbotResponder::class)->answer($question);

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);

        $documentSources = array_values(array_filter(
            $result['sources'],
            static fn (array $source): bool => $source['document_id'] === 'KB-007',
        ));
        $sectionIndexes = array_map(
            fn (array $source): int => $this->sourceFor($context->sources, $source)['section_index'],
            $documentSources,
        );

        self::assertGreaterThanOrEqual(2, count($sectionIndexes));
        self::assertSame($this->sorted($sectionIndexes), $sectionIndexes);

        $sectionPositions = array_map(
            fn (array $source): int => mb_strpos(
                $result['answer'],
                $this->formatSection($this->sourceFor($context->sources, $source)['content']),
            ),
            $documentSources,
        );

        self::assertSame($this->sorted($sectionPositions), $sectionPositions);
    }

    public function test_it_treats_an_alur_pengajuan_question_as_an_overview(): void
    {
        config(['services.groq.enabled' => false]);

        $result = app(GroundedChatbotResponder::class)->answer(
            'Saya mau bertanya mengenai tahapan pengajuan Magang.',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertContains([
            'document_id' => 'KB-007',
            'document_title' => 'Alur Utama Magang / Praktik Kerja Lapang (PKL)',
            'section_title' => 'Tahap 1: Pengajuan — Langkah 2: Koordinasi Ketersediaan Kuota',
        ], $result['sources']);
        self::assertContains([
            'document_id' => 'KB-007',
            'document_title' => 'Alur Utama Magang / Praktik Kerja Lapang (PKL)',
            'section_title' => 'Tahap 3: Pelaksanaan — Langkah 10: Isi Form Selesai Magang / PKL',
        ], $result['sources']);
        self::assertSame('KB-007', $result['sources'][0]['document_id']);
        self::assertLessThanOrEqual(
            3,
            count(array_unique(array_column($result['sources'], 'document_id'))),
        );
    }

    public function test_it_keeps_all_eligible_sections_from_the_dominant_overview_document(): void
    {
        config(['services.groq.enabled' => false]);

        $result = app(GroundedChatbotResponder::class)->answer(
            'Jelaskan alur pengajuan Magang / PKL secara lengkap.',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertSame('KB-007', $result['sources'][0]['document_id']);
        self::assertContains([
            'document_id' => 'KB-007',
            'document_title' => 'Alur Utama Magang / Praktik Kerja Lapang (PKL)',
            'section_title' => 'Tahap 1: Pengajuan — Langkah 2: Koordinasi Ketersediaan Kuota',
        ], $result['sources']);
        self::assertContains([
            'document_id' => 'KB-007',
            'document_title' => 'Alur Utama Magang / Praktik Kerja Lapang (PKL)',
            'section_title' => 'Tahap 2: Konfirmasi — Langkah 5: Penerbitan Surat Balasan',
        ], $result['sources']);
        self::assertContains([
            'document_id' => 'KB-007',
            'document_title' => 'Alur Utama Magang / Praktik Kerja Lapang (PKL)',
            'section_title' => 'Tahap 3: Pelaksanaan — Langkah 8: Siapkan Bahan Presentasi',
        ], $result['sources']);
    }

    public function test_it_keeps_a_specific_question_focused_on_the_best_covered_section(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'Jam kerja peserta magang hari Jumat sampai pukul berapa?',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertSame([
            [
                'document_id' => 'KB-001',
                'document_title' => 'Ketentuan Umum Peserta Magang dan PKL',
                'section_title' => 'Waktu Pelaksanaan',
            ],
        ], $result['sources']);
        self::assertStringContainsString('Jumat: pukul 07.00 sampai 16.30 WIB.', $result['answer']);
        self::assertStringNotContainsString('Jam Kerja Layanan', $result['answer']);
    }

    public function test_it_excludes_near_matches_from_a_specific_answer(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'Apa kewajiban peserta terkait etika dan tata krama?',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertSame([
            [
                'document_id' => 'KB-001',
                'document_title' => 'Ketentuan Umum Peserta Magang dan PKL',
                'section_title' => 'Etika dan Tata Krama',
            ],
        ], $result['sources']);
        self::assertStringContainsString(
            'Peserta wajib menjaga etika dan tata krama terhadap seluruh pegawai dan rekan kerja.',
            $result['answer'],
        );
        self::assertStringNotContainsString('Penjelasan Aturan dan Tata Tertib', $result['answer']);
    }

    public function test_it_keeps_moderately_specific_questions_focused_on_relevant_sections(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'Bagaimana ketentuan laporan magang?',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertSame([
            [
                'document_id' => 'KB-007',
                'document_title' => 'Alur Utama Magang / Praktik Kerja Lapang (PKL)',
                'section_title' => 'Tahap 3: Pelaksanaan — Langkah 7: Susun Laporan Kegiatan',
            ],
            [
                'document_id' => 'KB-001',
                'document_title' => 'Ketentuan Umum Peserta Magang dan PKL',
                'section_title' => 'Laporan',
            ],
            [
                'document_id' => 'KB-002',
                'document_title' => 'Penerbitan Surat Keterangan dan Sertifikat',
                'section_title' => 'Tahap 2: Pelaksanaan — Laporan Hasil',
            ],
        ], $result['sources']);
        self::assertStringContainsString('Peserta menyusun laporan hasil kegiatan Magang atau PKL', $result['answer']);
        self::assertStringContainsString('Laporan dikirimkan dalam bentuk PDF.', $result['answer']);
        self::assertStringContainsString('sesuai template kampus atau sekolah.', $result['answer']);
        self::assertStringNotContainsString('Batasan Informasi', $result['answer']);
        self::assertStringNotContainsString('Pendaftaran Magang atau PKL', $result['answer']);
    }

    public function test_it_limits_low_coverage_specific_questions_to_the_best_sections(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'Jika saya ingin berkoordinasi terkait kuota, adakah kontak yang bisa saya hubungi?',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertSame([
            [
                'document_id' => 'KB-003',
                'document_title' => 'Prosedur Pelayanan Magang / Praktik Kerja Lapang',
                'section_title' => 'Langkah 5: Koordinasi dan Komunikasi via WhatsApp',
            ],
        ], $result['sources']);
        self::assertStringContainsString('0852 53000 485', $result['answer']);
        self::assertStringNotContainsString('Langkah 7: Susun Laporan Kegiatan', $result['answer']);
        self::assertStringNotContainsString('Kontak Koordinasi', $result['answer']);
    }

    public function test_it_returns_the_complete_submission_requirements_without_mixing_in_workflow_steps(): void
    {
        config(['services.groq.enabled' => false]);

        $result = app(GroundedChatbotResponder::class)->answer(
            'Sebutkan rincian persyaratan pengajuan magang.',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertSame(
            ['KB-004'],
            array_values(array_unique(array_column($result['sources'], 'document_id'))),
        );
        self::assertStringContainsString('Nama Lengkap pemohon', $result['answer']);
        self::assertStringContainsString('NIS atau NIM wajib ditulis', $result['answer']);
        self::assertStringContainsString('nomor WhatsApp perwakilan', $result['answer']);
        self::assertStringNotContainsString('Isi Buku Tamu Magang', $result['answer']);
        self::assertStringNotContainsString('Isi Form Pelaksanaan Magang', $result['answer']);
    }

    public function test_it_uses_the_previous_question_for_an_explicit_follow_up(): void
    {
        config(['services.groq.enabled' => false]);

        $result = app(GroundedChatbotResponder::class)->answer(
            'Apakah hanya itu?',
            'Apa saja persyaratan pengajuan magang?',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertSame(
            ['KB-004'],
            array_values(array_unique(array_column($result['sources'], 'document_id'))),
        );
        self::assertStringContainsString('Kompetensi Keahlian', $result['answer']);
        self::assertStringContainsString('Jumlah Peserta', $result['answer']);
    }

    public function test_it_directs_current_quota_questions_to_the_official_contact_channel(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'Apakah masih ada kuota di bulan September?',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertSame([[
            'document_id' => 'KB-003',
            'document_title' => 'Prosedur Pelayanan Magang / Praktik Kerja Lapang',
            'section_title' => 'Langkah 5: Koordinasi dan Komunikasi via WhatsApp',
        ]], $result['sources']);
        self::assertStringContainsString('Saya belum dapat memastikan ketersediaan kuota saat ini.', $result['answer']);
        self::assertStringContainsString('0852 53000 485', $result['answer']);
        self::assertStringContainsString('@diskanlajatim', $result['answer']);
        self::assertStringNotContainsString('INSUFFICIENT_INFORMATION', $result['answer']);
    }

    public function test_it_directs_submission_destination_questions_to_the_official_submission_form(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'Surat permohonan itu nanti dikirim ke mana?',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertSame([[
            'document_id' => 'KB-007',
            'document_title' => 'Alur Utama Magang / Praktik Kerja Lapang (PKL)',
            'section_title' => 'Tahap 1: Pengajuan — Langkah 4: Isi Form Pelaksanaan Magang / PKL',
        ]], $result['sources']);
        self::assertStringContainsString('belum berarti permohonan Magang atau PKL diterima', $result['answer']);
        self::assertStringContainsString('Surat Balasan', $result['answer']);
        self::assertSame(1, substr_count($result['answer'], 'belum berarti permohonan Magang atau PKL diterima'));
        self::assertStringContainsString('https://tinyurl.com/DaftarMagangDKP-SM', $result['answer']);
        self::assertStringContainsString('https://tinyurl.com/DaftarMagangDKP-PT', $result['answer']);
        self::assertStringNotContainsString('0852 53000 485', $result['answer']);
        self::assertStringNotContainsString('https://bit.ly/WOPPS', $result['answer']);
    }

    public function test_it_uses_the_configured_llm_to_write_the_selected_grounded_answer(): void
    {
        config([
            'services.groq.enabled' => true,
            'services.groq.api_key' => 'test-groq-key',
        ]);
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => 'Pada hari Jumat, jam kerja peserta magang berakhir pukul 16.30 WIB.',
                    ],
                ]],
            ]),
        ]);

        $result = app(GroundedChatbotResponder::class)->answer(
            'Jam kerja peserta magang hari Jumat sampai pukul berapa?',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertSame(
            'Pada hari Jumat, jam kerja peserta magang berakhir pukul 16.30 WIB.',
            $result['answer'],
        );
        self::assertSame('KB-001', $result['sources'][0]['document_id']);
        Http::assertSentCount(1);
    }

    private function bodyLength(string $answer): int
    {
        $header = 'Berikut informasi yang tersedia pada dokumen resmi:';
        $footer = 'Anda dapat membuka bagian sumber di bawah untuk melihat dokumen lengkap.';

        self::assertStringStartsWith($header."\n\n", $answer);
        self::assertStringEndsWith("\n\n".$footer, $answer);

        return mb_strlen(substr(
            $answer,
            mb_strlen($header) + 2,
            -mb_strlen($footer) - 2,
        ));
    }

    private function formatSection(string $content): string
    {
        $content = str_replace(["\r\n", "\r"], "\n", trim($content));
        $content = preg_replace_callback(
            '/^#{1,6}\s+(.+)$/m',
            static fn (array $matches): string => '**'.trim($matches[1]).'**',
            $content,
        ) ?? $content;

        return trim(preg_replace('/\n{3,}/', "\n\n", $content) ?? $content);
    }

    /**
     * @param  array<int, int>  $values
     * @return array<int, int>
     */
    private function sorted(array $values): array
    {
        sort($values);

        return $values;
    }

    /**
     * @param  array<int, array{document_id: string, section_title: string, content: string}>  $sources
     * @param  array{document_id: string, section_title: string}  $publicSource
     * @return array{document_id: string, section_title: string, content: string}|null
     */
    private function sourceFor(array $sources, array $publicSource): ?array
    {
        foreach ($sources as $source) {
            if (
                $source['document_id'] === $publicSource['document_id']
                && $source['section_title'] === $publicSource['section_title']
            ) {
                return $source;
            }
        }

        return null;
    }
}
