<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\KnowledgeBase\KnowledgeBaseRetrievalPipeline;
use Tests\TestCase;

final class KnowledgeBaseRetrievalPipelineContainerTest extends TestCase
{
    public function test_it_resolves_the_retrieval_pipeline_from_the_application_container(): void
    {
        $pipeline = app(KnowledgeBaseRetrievalPipeline::class);

        self::assertInstanceOf(KnowledgeBaseRetrievalPipeline::class, $pipeline);
    }

    public function test_the_container_resolved_pipeline_retrieves_from_the_configured_knowledge_base(): void
    {
        $results = app(KnowledgeBaseRetrievalPipeline::class)->retrieve('sertifikat');

        self::assertNotEmpty($results);
        self::assertSame('KB-008', $results[0]->chunk->documentId);
    }
}
