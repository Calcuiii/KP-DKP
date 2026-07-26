<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

final class KnowledgeBaseGroundedContextBuilder
{
    public function __construct(
        private readonly KnowledgeBaseRetrievalPipeline $pipeline,
    ) {}

    public function build(
        string $query,
        int $topK = 5,
    ): KnowledgeBaseGroundedContext {
        $results = $this->pipeline->retrieve($query, $topK);

        $sources = array_map(
            static fn (KnowledgeBaseSearchResult $result): array => self::source(
                $result->chunk,
                $result->score,
            ),
            $results,
        );
        $directMatchTokenCounts = [];

        foreach ($results as $result) {
            $directMatchTokenCounts[$result->chunk->chunkId] = $result->directMatchTokenCount;
        }

        return new KnowledgeBaseGroundedContext(
            $query,
            $sources,
            $directMatchTokenCounts,
        );
    }

    /**
     * @return array{
     *     chunk_id: string,
     *     document_id: string,
     *     document_title: string,
     *     section_title: string,
     *     section_index: int,
     *     content: string,
     *     score: int,
     *     category: string,
     *     document_type: string,
     *     priority: int,
     *     status: string,
     *     source_file: string,
     *     source_sha256: string
     * }
     */
    private static function source(KnowledgeBaseChunk $chunk, int $score): array
    {
        return [
            'chunk_id' => $chunk->chunkId,
            'document_id' => $chunk->documentId,
            'document_title' => $chunk->documentTitle,
            'section_title' => $chunk->sectionTitle,
            'section_index' => $chunk->sectionIndex,
            'content' => $chunk->content,
            'score' => $score,
            'category' => $chunk->category,
            'document_type' => $chunk->documentType,
            'priority' => $chunk->priority,
            'status' => $chunk->status,
            'source_file' => $chunk->sourceFile,
            'source_sha256' => $chunk->sourceSha256,
        ];
    }
}
