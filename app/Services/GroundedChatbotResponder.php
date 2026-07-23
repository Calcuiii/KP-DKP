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

        $answer = "Berikut informasi yang tersedia pada dokumen resmi:\n"
            .implode("\n", $answerSections)
            ."\nAnda dapat membuka bagian sumber di bawah untuk melihat dokumen lengkap.";

        return [
            'status' => self::STATUS_SUCCESS,
            'answer' => Str::limit($answer, 3500, ''),
            'sources' => $publicSources,
        ];
    }

    private function cleanMarkdown(string $content): string
    {
        $content = str_replace(["\r\n", "\r"], "\n", trim($content));
        $content = preg_replace('/^#{1,6}\s+.*(?:\n|$)/m', '', $content) ?? $content;
        $content = preg_replace('/\n[ \t]*\n+/', "\n", $content) ?? $content;

        return Str::limit(trim($content), 950, '');
    }
}
