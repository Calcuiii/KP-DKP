<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GroundedChatbotContextSourcePrioritizer;
use Tests\TestCase;

final class GroundedChatbotContextSourcePrioritizerTest extends TestCase
{
    public function test_it_prioritizes_all_sections_of_the_dominant_document_over_lower_ranked_documents(): void
    {
        $sources = [
            $this->source('DOC-PRIMARY', 'primary-1', 30),
            $this->source('DOC-SECONDARY-A', 'secondary-a-1', 12),
            $this->source('DOC-SECONDARY-B', 'secondary-b-1', 11),
            $this->source('DOC-SECONDARY-C', 'secondary-c-1', 10),
            $this->source('DOC-PRIMARY', 'primary-2', 9),
            $this->source('DOC-PRIMARY', 'primary-3', 8),
        ];

        $result = app(GroundedChatbotContextSourcePrioritizer::class)->prioritize($sources);

        self::assertSame([
            'primary-1',
            'primary-2',
            'primary-3',
            'secondary-a-1',
            'secondary-b-1',
        ], array_column($result, 'chunk_id'));
        self::assertSame([
            'DOC-PRIMARY',
            'DOC-SECONDARY-A',
            'DOC-SECONDARY-B',
        ], array_values(array_unique(array_column($result, 'document_id'))));
    }

    public function test_it_preserves_ranked_order_within_the_additional_documents(): void
    {
        $sources = [
            $this->source('DOC-PRIMARY', 'primary-1', 20),
            $this->source('DOC-SECONDARY-A', 'secondary-a-1', 18),
            $this->source('DOC-SECONDARY-B', 'secondary-b-1', 17),
            $this->source('DOC-SECONDARY-A', 'secondary-a-2', 12),
        ];

        $result = app(GroundedChatbotContextSourcePrioritizer::class)->prioritize($sources);

        self::assertSame([
            'primary-1',
            'secondary-a-1',
            'secondary-b-1',
            'secondary-a-2',
        ], array_column($result, 'chunk_id'));
    }

    /**
     * @return array{chunk_id: string, document_id: string, score: int}
     */
    private function source(string $documentId, string $chunkId, int $score): array
    {
        return [
            'chunk_id' => $chunkId,
            'document_id' => $documentId,
            'score' => $score,
        ];
    }
}
