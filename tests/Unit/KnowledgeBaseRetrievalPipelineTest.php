<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseChunker;
use App\KnowledgeBase\KnowledgeBaseDocumentLoader;
use App\KnowledgeBase\KnowledgeBasePolicyResolver;
use App\KnowledgeBase\KnowledgeBaseRegistry;
use App\KnowledgeBase\KnowledgeBaseRetrievalPipeline;
use App\KnowledgeBase\KnowledgeBaseTopicResolver;
use App\KnowledgeBase\LexicalKnowledgeBaseRetriever;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class KnowledgeBaseRetrievalPipelineTest extends TestCase
{
    private string $registryPath;

    private string $processedDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registryPath = dirname(__DIR__, 2)
            .'/storage/app/knowledge-base/metadata/documents.json';

        $this->processedDirectory = dirname(__DIR__, 2)
            .'/storage/app/knowledge-base/processed';
    }

    public function test_it_retrieves_results_from_real_knowledge_base_pipeline(): void
    {
        $results = $this->pipeline()->retrieve(
            'sertifikat',
            5,
        );

        self::assertNotEmpty($results);
        self::assertLessThanOrEqual(5, count($results));

        self::assertSame(
            'KB-008',
            $results[0]->chunk->documentId,
        );

        self::assertSame(
            'Penerbitan Sertifikat',
            $results[0]->chunk->sectionTitle,
        );
    }

    public function test_it_resolves_topic_and_applies_override_policy_automatically(): void
    {
        $results = $this->pipeline()->retrieve(
            'Bagaimana cara mendapatkan surat keterangan magang?',
            20,
        );

        $documentIds = array_map(
            static fn ($result): string => $result->chunk->documentId,
            $results,
        );

        self::assertContains('KB-008', $documentIds);
        self::assertNotContains('KB-002', $documentIds);
        self::assertNotContains('KB-007', $documentIds);
    }

    public function test_it_applies_clarification_ordering_before_final_top_k(): void
    {
        $results = $this->pipeline()->retrieve(
            'sertifikat',
            20,
        );

        $documentIds = array_map(
            static fn ($result): string => $result->chunk->documentId,
            $results,
        );

        $policyPosition = array_search('KB-008', $documentIds, true);
        $targetPosition = array_search('KB-002', $documentIds, true);

        self::assertIsInt($policyPosition);
        self::assertIsInt($targetPosition);
        self::assertLessThan($targetPosition, $policyPosition);
    }

    public function test_it_respects_final_top_k(): void
    {
        $results = $this->pipeline()->retrieve(
            'magang',
            3,
        );

        self::assertCount(3, $results);
    }

    public function test_it_returns_empty_results_for_unmatched_query(): void
    {
        self::assertSame(
            [],
            $this->pipeline()->retrieve(
                'kataunikyangtidakada123456789',
                5,
            ),
        );
    }

    public function test_it_retrieves_lexically_when_topic_is_unmatched(): void
    {
        $results = $this->pipeline()->retrieve(
            'magang',
            5,
        );

        self::assertNotEmpty($results);
        self::assertLessThanOrEqual(5, count($results));
    }

    public function test_it_uses_resolved_topic_without_caller_supplied_topic(): void
    {
        $results = $this->pipeline()->retrieve(
            'Apakah peserta magang mendapatkan sertifikat?',
            5,
        );

        self::assertNotEmpty($results);

        self::assertSame(
            'KB-008',
            $results[0]->chunk->documentId,
        );
    }

    public function test_it_rejects_invalid_top_k(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'topK must be greater than zero.',
        );

        $this->pipeline()->retrieve(
            'sertifikat',
            0,
        );
    }

    public function test_it_rejects_empty_query(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query must not be empty.');

        $this->pipeline()->retrieve(
            '   ',
            5,
        );
    }

    public function test_it_normalizes_topic_resolution_through_pipeline(): void
    {
        $results = $this->pipeline()->retrieve(
            '  SERTIFIKAT??? Bagaimana caranya!!  ',
            5,
        );

        self::assertNotEmpty($results);

        self::assertSame(
            'KB-008',
            $results[0]->chunk->documentId,
        );
    }

    private function pipeline(): KnowledgeBaseRetrievalPipeline
    {
        return new KnowledgeBaseRetrievalPipeline(
            registry: new KnowledgeBaseRegistry(
                $this->registryPath,
            ),
            documentLoader: new KnowledgeBaseDocumentLoader(
                $this->processedDirectory,
            ),
            chunker: new KnowledgeBaseChunker,
            retriever: new LexicalKnowledgeBaseRetriever,
            policyResolver: new KnowledgeBasePolicyResolver,
            topicResolver: new KnowledgeBaseTopicResolver,
        );
    }
}
