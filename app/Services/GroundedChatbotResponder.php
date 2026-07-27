<?php

declare(strict_types=1);

namespace App\Services;

use App\KnowledgeBase\KnowledgeBaseAnswerGenerationResult;
use App\KnowledgeBase\KnowledgeBaseAnswerGenerator;
use App\KnowledgeBase\KnowledgeBaseGroundedContext;
use App\KnowledgeBase\KnowledgeBaseGroundedContextBuilder;

final class GroundedChatbotResponder
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_INSUFFICIENT_INFORMATION = 'insufficient_information';

    private const RETRIEVAL_TOP_K = 20;

    private const BODY_CHARACTER_BUDGET = 5500;

    private const SPECIFIC_QUESTION_COVERAGE_RATIO = 0.9;

    private const MODERATE_QUESTION_SCORE_RATIO = 0.75;

    private const LOW_COVERAGE_MAX_SECTIONS = 3;

    private const CONTACT_LOOKUP_QUERY = 'kontak koordinasi WhatsApp layanan Magang PKL';

    private const SUBMISSION_FORM_LOOKUP_QUERY = 'alur Magang PKL form pelaksanaan';

    /** @var array<int, string> */
    private const OVERVIEW_QUERY_TOKENS = [
        'alur',
        'tahap',
        'berurutan',
        'awal',
        'akhir',
        'hingga',
        'sampai',
    ];

    /** @var array<int, string> */
    private const OVERVIEW_SUBJECT_TOKENS = [
        'magang',
        'pkl',
        'pengajuan',
        'prosedur',
    ];

    /** @var array<int, string> */
    private const OVERVIEW_ANCHOR_TOKENS = [
        'alur',
        'tahap',
        'berurutan',
    ];

    public function __construct(
        private readonly KnowledgeBaseGroundedContextBuilder $contextBuilder,
        private readonly GroundedChatbotResponseSectionOrderer $sectionOrderer,
        private readonly KnowledgeBaseAnswerGenerator $answerGenerator,
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
    public function answer(
        string $question,
        ?string $previousQuestion = null,
    ): array {
        $isSubmissionDestinationQuestion = $this->requiresSubmissionDestinationConfirmation($question);

        if ($isSubmissionDestinationQuestion) {
            $submissionFormSource = $this->submissionFormSource();

            if ($submissionFormSource !== null) {
                return [
                    'status' => self::STATUS_SUCCESS,
                    'answer' => $this->submissionDestinationAnswer($submissionFormSource['content']),
                    'sources' => [[
                        'document_id' => $submissionFormSource['document_id'],
                        'document_title' => $submissionFormSource['document_title'],
                        'section_title' => $submissionFormSource['section_title'],
                    ]],
                ];
            }
        }

        if ($this->requiresCurrentAvailabilityConfirmation($question)) {
            $contactSource = $this->contactSource();

            if ($contactSource !== null) {
                return [
                    'status' => self::STATUS_SUCCESS,
                    'answer' => $this->contactConfirmationAnswer($contactSource['content']),
                    'sources' => [[
                        'document_id' => $contactSource['document_id'],
                        'document_title' => $contactSource['document_title'],
                        'section_title' => $contactSource['section_title'],
                    ]],
                ];
            }
        }

        $isFollowUpQuestion = $this->isFollowUpQuestion($question, $previousQuestion);
        $retrievalQuestion = $isFollowUpQuestion ? $previousQuestion : $question;
        $questionForAnswer = $isFollowUpQuestion
            ? "Pertanyaan sebelumnya: {$previousQuestion}\nPertanyaan lanjutan: {$question}"
            : $question;

        $context = $this->contextBuilder->build(
            $retrievalQuestion,
            self::RETRIEVAL_TOP_K,
        );
        $maximumDirectMatchTokenCount = max(
            $context->directMatchTokenCounts === []
                ? [0]
                : $context->directMatchTokenCounts,
        );
        $maximumScore = max(array_map(
            static fn (array $source): int => $source['score'],
            $context->sources,
        ) ?: [0]);
        $isOverviewQuestion = $this->isOverviewQuestion($retrievalQuestion);
        $isCollectionQuestion = $this->isCollectionQuestion($retrievalQuestion);
        $minimumDirectMatchTokenCount = $this->minimumDirectMatchTokenCount(
            $maximumDirectMatchTokenCount,
            $isOverviewQuestion,
        );
        $minimumScore = $maximumDirectMatchTokenCount === 3 && ! $isOverviewQuestion
            ? (int) ceil($maximumScore * self::MODERATE_QUESTION_SCORE_RATIO)
            : 0;
        $shouldLimitLowCoverageSections = ! $isOverviewQuestion
            && ! $isCollectionQuestion
            && $maximumDirectMatchTokenCount > 0
            && $maximumDirectMatchTokenCount <= 2;

        $usableSources = array_values(array_filter(
            $context->sources,
            static fn (array $source): bool => $source['score'] > 0
                && trim($source['content']) !== ''
                && ($minimumDirectMatchTokenCount === 0
                    || ($context->directMatchTokenCounts[$source['chunk_id']] ?? 0) >= $minimumDirectMatchTokenCount)
                && $source['score'] >= $minimumScore,
        ));

        if (($isOverviewQuestion || $isCollectionQuestion) && $usableSources !== []) {
            $primaryDocumentId = $usableSources[0]['document_id'];
            $primaryDocumentSources = array_values(array_filter(
                $usableSources,
                static fn (array $source): bool => $source['document_id'] === $primaryDocumentId,
            ));

            if (count($primaryDocumentSources) >= 2) {
                $usableSources = $primaryDocumentSources;
            }
        }

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
            if (
                $shouldLimitLowCoverageSections
                && count($selectedSections) >= self::LOW_COVERAGE_MAX_SECTIONS
            ) {
                break;
            }

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
                'source' => $source,
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

        $generation = $this->answerGenerator->generate(
            new KnowledgeBaseGroundedContext(
                $questionForAnswer,
                array_map(
                    static function (array $section): array {
                        $source = $section['source'];
                        $source['content'] = $section['content'];

                        return $source;
                    },
                    $orderedSections,
                ),
            ),
        );

        if ($generation->status === KnowledgeBaseAnswerGenerationResult::STATUS_SUCCESS) {
            return [
                'status' => self::STATUS_SUCCESS,
                'answer' => $generation->answer,
                'sources' => $generation->sources,
            ];
        }

        if ($generation->status === KnowledgeBaseAnswerGenerationResult::STATUS_INSUFFICIENT_INFORMATION) {
            return [
                'status' => self::STATUS_INSUFFICIENT_INFORMATION,
                'answer' => 'Maaf, saya belum menemukan informasi yang cukup untuk menjawab pertanyaan tersebut berdasarkan dokumen resmi yang tersedia.',
                'sources' => [],
            ];
        }

        return [
            'status' => self::STATUS_SUCCESS,
            'answer' => $this->deterministicAnswer($answerSections),
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

    /**
     * @param  array<int, string>  $answerSections
     */
    private function deterministicAnswer(array $answerSections): string
    {
        $header = 'Berikut informasi yang tersedia pada dokumen resmi:';
        $footer = 'Anda dapat membuka bagian sumber di bawah untuk melihat dokumen lengkap.';

        return $header."\n\n".implode("\n\n", $answerSections)."\n\n".$footer;
    }

    /**
     * Availability is not a static fact in the knowledge base. When a user
     * asks about it, direct them to an official coordination channel rather
     * than letting a language model infer a current status.
     */
    private function requiresCurrentAvailabilityConfirmation(string $question): bool
    {
        $tokens = preg_split(
            '/[^\p{L}\p{N}]+/u',
            mb_strtolower($question, 'UTF-8'),
            -1,
            PREG_SPLIT_NO_EMPTY,
        );

        if ($tokens === false) {
            return false;
        }

        return array_intersect($tokens, ['kuota', 'ketersediaan']) !== [];
    }

    private function requiresSubmissionDestinationConfirmation(string $question): bool
    {
        $tokens = preg_split(
            '/[^\p{L}\p{N}]+/u',
            mb_strtolower($question, 'UTF-8'),
            -1,
            PREG_SPLIT_NO_EMPTY,
        );

        if ($tokens === false) {
            return false;
        }

        $asksForSubmissionDestination = array_intersect($tokens, [
            'kirim',
            'kirimkan',
            'dikirim',
            'unggah',
            'upload',
            'tujuan',
            'kemana',
            'dimana',
        ]) !== [];
        $mentionsMagangSubmission = array_intersect($tokens, [
            'magang',
            'pkl',
            'surat',
            'permohonan',
        ]) !== [];

        return $asksForSubmissionDestination && $mentionsMagangSubmission;
    }

    /**
     * @return array{
     *     document_id: string,
     *     document_title: string,
     *     section_title: string,
     *     content: string
     * }|null
     */
    private function contactSource(): ?array
    {
        $contactContext = $this->contextBuilder->build(
            self::CONTACT_LOOKUP_QUERY,
            5,
        );

        foreach ($contactContext->sources as $source) {
            if (preg_match('/\bwhatsApp\b/ui', $source['content']) === 1) {
                return $source;
            }
        }

        return null;
    }

    /**
     * @return array{
     *     document_id: string,
     *     document_title: string,
     *     section_title: string,
     *     content: string
     * }|null
     */
    private function submissionFormSource(): ?array
    {
        $context = $this->contextBuilder->build(
            self::SUBMISSION_FORM_LOOKUP_QUERY,
            5,
        );

        foreach ($context->sources as $source) {
            if (
                preg_match('/form pelaksanaan/ui', $source['content']) === 1
                && preg_match('/https?:\/\//ui', $source['content']) === 1
            ) {
                return $source;
            }
        }

        return null;
    }

    private function submissionDestinationAnswer(string $submissionFormContent): string
    {
        return "Form Pelaksanaan Magang atau PKL yang digunakan adalah sebagai berikut:\n\n"
            .$this->cleanMarkdown($submissionFormContent);
    }

    private function contactConfirmationAnswer(string $contactContent): string
    {
        return "Saya belum dapat memastikan ketersediaan kuota saat ini. Untuk konfirmasi, silakan hubungi layanan resmi berikut:\n\n"
            .$this->cleanMarkdown($contactContent);
    }

    private function isOverviewQuestion(string $question): bool
    {
        $normalizedQuestion = mb_strtolower($question, 'UTF-8');

        if (preg_match('/\blangkah\s+\d+\b/u', $normalizedQuestion) === 1) {
            return false;
        }

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $normalizedQuestion, -1, PREG_SPLIT_NO_EMPTY);

        if ($tokens === false) {
            return false;
        }

        $overviewTokenCount = count(array_intersect($tokens, self::OVERVIEW_QUERY_TOKENS));
        $hasOverviewAnchor = array_intersect($tokens, self::OVERVIEW_ANCHOR_TOKENS) !== [];

        return $overviewTokenCount >= 2
            || (
                $hasOverviewAnchor
                && array_intersect($tokens, self::OVERVIEW_SUBJECT_TOKENS) !== []
            );
    }

    private function isCollectionQuestion(string $question): bool
    {
        $normalizedQuestion = mb_strtolower($question, 'UTF-8');

        if (str_contains($normalizedQuestion, 'apa saja')) {
            return true;
        }

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $normalizedQuestion, -1, PREG_SPLIT_NO_EMPTY);

        if ($tokens === false) {
            return false;
        }

        return array_intersect($tokens, [
            'sebutkan',
            'rincian',
            'lengkap',
            'kelengkapan',
            'persyaratan',
        ]) !== [];
    }

    private function isFollowUpQuestion(string $question, ?string $previousQuestion): bool
    {
        if ($previousQuestion === null || trim($previousQuestion) === '') {
            return false;
        }

        $normalizedQuestion = mb_strtolower(trim($question), 'UTF-8');

        foreach ([
            'hanya itu',
            'itu saja',
            'apakah itu saja',
            'apakah hanya itu',
            'bagaimana dengan',
            'lebih lanjut',
            'jelaskan lagi',
        ] as $followUpPhrase) {
            if (str_contains($normalizedQuestion, $followUpPhrase)) {
                return true;
            }
        }

        return false;
    }

    private function minimumDirectMatchTokenCount(
        int $maximumDirectMatchTokenCount,
        bool $isOverviewQuestion,
    ): int {
        if ($isOverviewQuestion) {
            return 0;
        }

        if ($maximumDirectMatchTokenCount >= 4) {
            return (int) ceil(
                $maximumDirectMatchTokenCount * self::SPECIFIC_QUESTION_COVERAGE_RATIO,
            );
        }

        return $maximumDirectMatchTokenCount === 3 ? 2 : 0;
    }
}
