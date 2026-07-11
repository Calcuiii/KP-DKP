<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseAnswerGenerationResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class KnowledgeBaseAnswerGenerationResultTest extends TestCase
{
    public function test_it_creates_a_successful_answer_with_safe_sources(): void
    {
        $sources = [[
            'document_id' => 'KB-008',
            'document_title' => 'Kebijakan Sertifikat Magang',
            'section_title' => 'Penerbitan Sertifikat',
        ]];

        $result = KnowledgeBaseAnswerGenerationResult::success(
            'Peserta magang dapat memperoleh sertifikat sesuai ketentuan.',
            $sources,
        );

        self::assertSame(
            KnowledgeBaseAnswerGenerationResult::STATUS_SUCCESS,
            $result->status,
        );
        self::assertSame(
            'Peserta magang dapat memperoleh sertifikat sesuai ketentuan.',
            $result->answer,
        );
        self::assertSame($sources, $result->sources);
        self::assertNull($result->failureCode);
    }

    public function test_it_creates_a_deterministic_insufficient_information_result(): void
    {
        $result = KnowledgeBaseAnswerGenerationResult::insufficientInformation();

        self::assertSame(
            KnowledgeBaseAnswerGenerationResult::STATUS_INSUFFICIENT_INFORMATION,
            $result->status,
        );
        self::assertSame(
            'Knowledge Base resmi tidak memiliki informasi yang cukup untuk menjawab pertanyaan tersebut.',
            $result->answer,
        );
        self::assertSame([], $result->sources);
        self::assertNull($result->failureCode);
    }

    public function test_it_creates_a_structured_provider_failure(): void
    {
        $result = KnowledgeBaseAnswerGenerationResult::providerFailure(
            'provider_timeout',
        );

        self::assertSame(
            KnowledgeBaseAnswerGenerationResult::STATUS_PROVIDER_FAILURE,
            $result->status,
        );
        self::assertNull($result->answer);
        self::assertSame([], $result->sources);
        self::assertSame('provider_timeout', $result->failureCode);
    }

    public function test_it_rejects_an_empty_successful_answer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Successful answer must not be empty.',
        );

        KnowledgeBaseAnswerGenerationResult::success('   ', []);
    }

    public function test_it_rejects_empty_provider_failure_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Provider failure code must not be empty.',
        );

        KnowledgeBaseAnswerGenerationResult::providerFailure('   ');
    }

    public function test_it_rejects_source_with_internal_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Answer source at index 0 must contain exactly the supported public fields.',
        );

        KnowledgeBaseAnswerGenerationResult::success(
            'Jawaban.',
            [[
                'document_id' => 'KB-008',
                'document_title' => 'Kebijakan Sertifikat Magang',
                'section_title' => 'Penerbitan Sertifikat',
                'score' => 10,
            ]],
        );
    }

    public function test_it_rejects_source_with_missing_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Answer source at index 0 must contain exactly the supported public fields.',
        );

        KnowledgeBaseAnswerGenerationResult::success(
            'Jawaban.',
            [[
                'document_id' => 'KB-008',
                'document_title' => 'Kebijakan Sertifikat Magang',
            ]],
        );
    }

    public function test_it_rejects_empty_source_field(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Answer source field section_title at index 0 must be a non-empty string.',
        );

        KnowledgeBaseAnswerGenerationResult::success(
            'Jawaban.',
            [[
                'document_id' => 'KB-008',
                'document_title' => 'Kebijakan Sertifikat Magang',
                'section_title' => '   ',
            ]],
        );
    }
}
