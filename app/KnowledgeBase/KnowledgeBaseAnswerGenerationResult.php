<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

use InvalidArgumentException;

final readonly class KnowledgeBaseAnswerGenerationResult
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_INSUFFICIENT_INFORMATION = 'insufficient_information';

    public const STATUS_PROVIDER_FAILURE = 'provider_failure';

    /**
     * @param  array<int, array{
     *     document_id: string,
     *     document_title: string,
     *     section_title: string
     * }>  $sources
     */
    private function __construct(
        public string $status,
        public ?string $answer,
        public array $sources,
        public ?string $failureCode,
        public ?int $tokensUsed = null,
        public array $providerLimits = [],
        public ?int $retryAfter = null,
    ) {}

    /**
     * @param  array<int, array{
     *     document_id: string,
     *     document_title: string,
     *     section_title: string
     * }>  $sources
     */
    public static function success(
        string $answer,
        array $sources,
        ?int $tokensUsed = null,
        array $providerLimits = [],
    ): self {
        if (trim($answer) === '') {
            throw new InvalidArgumentException(
                'Successful answer must not be empty.',
            );
        }

        self::validateSources($sources);

        return new self(
            self::STATUS_SUCCESS,
            $answer,
            $sources,
            null,
            $tokensUsed,
            $providerLimits,
        );
    }

    public static function insufficientInformation(?int $tokensUsed = null, array $providerLimits = []): self
    {
        return new self(
            self::STATUS_INSUFFICIENT_INFORMATION,
            'Knowledge Base resmi tidak memiliki informasi yang cukup untuk menjawab pertanyaan tersebut.',
            [],
            null,
            $tokensUsed,
            $providerLimits,
        );
    }

    public static function providerFailure(string $failureCode, ?int $tokensUsed = null, array $providerLimits = [], ?int $retryAfter = null): self
    {
        if (trim($failureCode) === '') {
            throw new InvalidArgumentException(
                'Provider failure code must not be empty.',
            );
        }

        return new self(
            self::STATUS_PROVIDER_FAILURE,
            null,
            [],
            $failureCode,
            $tokensUsed,
            $providerLimits,
            $retryAfter,
        );
    }

    /**
     * @param  array<int, array{
     *     document_id: string,
     *     document_title: string,
     *     section_title: string
     * }>  $sources
     */
    private static function validateSources(array $sources): void
    {
        foreach ($sources as $index => $source) {
            $expectedKeys = [
                'document_id',
                'document_title',
                'section_title',
            ];

            if (array_keys($source) !== $expectedKeys) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Answer source at index %d must contain exactly the supported public fields.',
                        $index,
                    ),
                );
            }

            foreach ($expectedKeys as $key) {
                if (! is_string($source[$key]) || trim($source[$key]) === '') {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Answer source field %s at index %d must be a non-empty string.',
                            $key,
                            $index,
                        ),
                    );
                }
            }
        }
    }
}
