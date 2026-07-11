<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseGroundedContext;
use App\KnowledgeBase\KnowledgeBaseGroundedContextBuilder;
use App\KnowledgeBase\KnowledgeBaseRetrievalPipeline;
use InvalidArgumentException;
use Tests\TestCase;

final class KnowledgeBaseGroundedContextBuilderTest extends TestCase
{
    public function test_it_builds_a_grounded_context(): void
    {
        $context = $this->builder()->build('sertifikat');

        self::assertInstanceOf(KnowledgeBaseGroundedContext::class, $context);
    }

    public function test_it_preserves_the_original_query_exactly(): void
    {
        $query = '  SERTIFIKAT???  ';

        $context = $this->builder()->build($query);

        self::assertSame($query, $context->query);
    }

    public function test_it_preserves_retrieval_result_order_exactly(): void
    {
        $query = 'sertifikat';
        $results = app(KnowledgeBaseRetrievalPipeline::class)->retrieve($query, 20);

        $context = $this->builder()->build($query, 20);

        self::assertSame(
            array_map(
                static fn ($result): string => $result->chunk->chunkId,
                $results,
            ),
            array_column($context->sources, 'chunk_id'),
        );
    }

    public function test_it_maps_every_supported_source_field_exactly(): void
    {
        $query = 'sertifikat';
        $result = app(KnowledgeBaseRetrievalPipeline::class)->retrieve($query)[0];

        $context = $this->builder()->build($query);

        self::assertSame([
            'chunk_id' => $result->chunk->chunkId,
            'document_id' => $result->chunk->documentId,
            'document_title' => $result->chunk->documentTitle,
            'section_title' => $result->chunk->sectionTitle,
            'section_index' => $result->chunk->sectionIndex,
            'content' => $result->chunk->content,
            'score' => $result->score,
            'category' => $result->chunk->category,
            'document_type' => $result->chunk->documentType,
            'priority' => $result->chunk->priority,
            'status' => $result->chunk->status,
            'source_file' => $result->chunk->sourceFile,
            'source_sha256' => $result->chunk->sourceSha256,
        ], $context->sources[0]);
    }

    public function test_it_returns_empty_sources_for_an_unmatched_query(): void
    {
        $query = 'kataunikyangtidakada123456789';

        $context = $this->builder()->build($query);

        self::assertSame($query, $context->query);
        self::assertSame([], $context->sources);
    }

    public function test_it_preserves_the_pipeline_empty_query_validation_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query must not be empty.');

        $this->builder()->build('   ');
    }

    public function test_it_preserves_the_pipeline_invalid_top_k_validation_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('topK must be greater than zero.');

        $this->builder()->build('sertifikat', 0);
    }

    public function test_it_is_resolvable_from_the_laravel_container_without_an_explicit_binding(): void
    {
        self::assertInstanceOf(
            KnowledgeBaseGroundedContextBuilder::class,
            app(KnowledgeBaseGroundedContextBuilder::class),
        );
    }

    private function builder(): KnowledgeBaseGroundedContextBuilder
    {
        return app(KnowledgeBaseGroundedContextBuilder::class);
    }
}
