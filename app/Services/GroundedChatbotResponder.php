<?php

declare(strict_types=1);

namespace App\Services;

use App\KnowledgeBase\KnowledgeBaseGroundedContextBuilder;
use Illuminate\Support\Str;

final class GroundedChatbotResponder
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_INSUFFICIENT_INFORMATION = 'insufficient_information';

    public function __construct(
        private readonly KnowledgeBaseGroundedContextBuilder $contextBuilder,
    ) {}

    /**
     * @return array{
     *     status: string,
     *     answer: string,
     *     sources: array<int, array{
     *         document_id: string,
     *         document_title: string,
     *         section_title: string
     *     }>
     * }
     */
    public function answer(string $question): array
    {
        $context = $this->contextBuilder->build($question, 5);

        $usableSources = array_values(array_filter(
            $context->sources,
            static fn (array $source): bool => $source['score'] > 0
                && trim($source['content']) !== '',
        ));

        if ($usableSources === []) {
            return [
                'status' => self::STATUS_INSUFFICIENT_INFORMATION,
                'answer' => 'Maaf, saya belum menemukan informasi yang cukup untuk menjawab pertanyaan tersebut berdasarkan dokumen resmi yang tersedia.',
                'sources' => [],
            ];
        }

        $publicSources = [];
        $answerSections = [];
        $seenSourceKeys = [];

        foreach ($usableSources as $source) {
            $sourceKey = $source['document_id'].'|'.$source['section_title'];

            if (isset($seenSourceKeys[$sourceKey])) {
                continue;
            }

            $seenSourceKeys[$sourceKey] = true;

            $publicSources[] = [
                'document_id' => $source['document_id'],
                'document_title' => $source['document_title'],
                'section_title' => $source['section_title'],
            ];

            $cleanContent = $this->cleanMarkdown($source['content']);

            if ($cleanContent !== '') {
                $answerSections[] = $cleanContent;
            }

            if (count($publicSources) >= 3) {
                break;
            }
        }

        if ($answerSections === []) {
            return [
                'status' => self::STATUS_INSUFFICIENT_INFORMATION,
                'answer' => 'Maaf, saya belum menemukan informasi yang cukup untuk menjawab pertanyaan tersebut berdasarkan dokumen resmi yang tersedia.',
                'sources' => [],
            ];
        }

        $header = 'Berikut informasi yang tersedia pada dokumen resmi:';
        $footer = 'Anda dapat membuka bagian sumber di bawah untuk melihat dokumen lengkap.';

        $sectionsBudget = 3500 - mb_strlen($header) - mb_strlen($footer) - 4;
        $body = $this->truncateSafely(implode("\n\n", $answerSections), max($sectionsBudget, 0));

        $answer = $header."\n\n".$body."\n\n".$footer;

        return [
            'status' => self::STATUS_SUCCESS,
            'answer' => $answer,
            'sources' => $publicSources,
        ];
    }

    private function cleanMarkdown(string $content): string
    {
        $content = str_replace(["\r\n", "\r"], "\n", trim($content));
        $content = preg_replace_callback(
            '/^#{1,6}\s+(.+)$/m',
            static fn (array $matches): string => '**'.trim($matches[1]).'**',
            $content,
        ) ?? $content;
        $content = preg_replace('/\n{3,}/', "\n\n", $content) ?? $content;

        return $this->truncateSafely(trim($content), 950);
    }

    private function truncateSafely(string $content, int $maxLength): string
    {
        if (mb_strlen($content) <= $maxLength) {
            return $content;
        }

        $blocks = preg_split('/\n{2,}/', $content) ?: [$content];

        $result = [];
        $length = 0;

        foreach ($blocks as $block) {
            // +2 memperhitungkan baris kosong pemisah saat blok digabung kembali.
            $addedLength = mb_strlen($block) + ($result === [] ? 0 : 2);

            if ($length + $addedLength > $maxLength) {
                break;
            }

            $result[] = $block;
            $length += $addedLength;
        }

        // Fallback: kalau blok pertama saja sudah melebihi batas (mis. satu
        // paragraf sangat panjang tanpa baris kosong), potong paksa supaya
        // tidak mengembalikan jawaban kosong.
        if ($result === []) {
            return mb_substr($blocks[0], 0, $maxLength);
        }

        return implode("\n\n", $result);
    }
}
