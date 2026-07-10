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
            static function (KnowledgeBaseSearchResult $result): array {
                $chunk = $result->chunk;

                return [
                    'chunk_id' => $chunk->chunkId,
                    'document_id' => $chunk->documentId,
                    'document_title' => $chunk->documentTitle,
                    'section_title' => $chunk->sectionTitle,
                    'section_index' => $chunk->sectionIndex,
                    'content' => $chunk->content,
                    'score' => $result->score,
                    'category' => $chunk->category,
                    'document_type' => $chunk->documentType,
                    'priority' => $chunk->priority,
                    'status' => $chunk->status,
                    'source_file' => $chunk->sourceFile,
                    'source_sha256' => $chunk->sourceSha256,
                ];
            },
            $results,
        );

        return new KnowledgeBaseGroundedContext($query, $sources);
    }
}
