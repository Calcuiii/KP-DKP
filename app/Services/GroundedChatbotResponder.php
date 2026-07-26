<?php

declare(strict_types=1);

namespace App\Services;

use App\KnowledgeBase\KnowledgeBaseGroundedContextBuilder;

final class GroundedChatbotResponder
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_INSUFFICIENT_INFORMATION = 'insufficient_information';

    private const RETRIEVAL_TOP_K = 20;

    private const BODY_CHARACTER_BUDGET = 5500;

    public function __construct(
        private readonly KnowledgeBaseGroundedContextBuilder $contextBuilder,
        private readonly GroundedChatbotResponseSectionOrderer $sectionOrderer,
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
        $context = $this->contextBuilder->build(
            $question,
            self::RETRIEVAL_TOP_K,
        );

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

        $selectedSections = [];
        $seenSourceKeys = [];
        $bodyLength = 0;

        foreach ($usableSources as $source) {
            $sourceKey = $source['document_id'].'|'.$source['section_title'];

            if (isset($seenSourceKeys[$sourceKey])) {
                continue;
            }

            $seenSourceKeys[$sourceKey] = true;

            $cleanContent = $this->cleanMarkdown($source['content']);

            if ($cleanContent === '') {
                continue;
            }

            $addedLength = mb_strlen($cleanContent)
                + ($selectedSections === [] ? 0 : 2);

            if ($bodyLength + $addedLength > self::BODY_CHARACTER_BUDGET) {
                break;
            }

            $selectedSections[] = [
                'source' => [
                    'document_id' => $source['document_id'],
                    'document_title' => $source['document_title'],
                    'section_title' => $source['section_title'],
                    'section_index' => $source['section_index'],
                ],
                'content' => $cleanContent,
            ];
            $bodyLength += $addedLength;
        }

        if ($selectedSections === []) {
            return [
                'status' => self::STATUS_INSUFFICIENT_INFORMATION,
                'answer' => 'Maaf, saya belum menemukan informasi yang cukup untuk menjawab pertanyaan tersebut berdasarkan dokumen resmi yang tersedia.',
                'sources' => [],
            ];
        }

        $orderedSections = $this->sectionOrderer->order($selectedSections);
        $publicSources = array_map(
            static fn (array $section): array => [
                'document_id' => $section['source']['document_id'],
                'document_title' => $section['source']['document_title'],
                'section_title' => $section['source']['section_title'],
            ],
            $orderedSections,
        );
        $answerSections = array_map(
            static fn (array $section): string => $section['content'],
            $orderedSections,
        );

        $header = 'Berikut informasi yang tersedia pada dokumen resmi:';
        $footer = 'Anda dapat membuka bagian sumber di bawah untuk melihat dokumen lengkap.';

        $answer = $header."\n\n".implode("\n\n", $answerSections)."\n\n".$footer;

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

        return trim($content);
    }
}
