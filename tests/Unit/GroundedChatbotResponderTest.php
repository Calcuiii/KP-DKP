<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseGroundedContextBuilder;
use App\Services\GroundedChatbotResponder;
use Tests\TestCase;

final class GroundedChatbotResponderTest extends TestCase
{
    public function test_it_returns_a_bounded_grounded_answer_without_markdown_headings(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'Apa saja persyaratan pengajuan magang?',
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
        $question = 'Apa saja persyaratan pengajuan magang?';
        $context = app(KnowledgeBaseGroundedContextBuilder::class)->build($question, 20);
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
        $context = app(KnowledgeBaseGroundedContextBuilder::class)->build($question, 20);
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
