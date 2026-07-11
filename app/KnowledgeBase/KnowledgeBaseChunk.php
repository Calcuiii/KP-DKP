<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

final readonly class KnowledgeBaseChunk
{
    /**
     * @param  array<int, array{
     *     relation: string,
     *     document_id: string,
     *     topics: array<int, string>
     * }>  $policyRelations
     */
    public function __construct(
        public string $chunkId,
        public string $documentId,
        public string $documentTitle,
        public string $sectionTitle,
        public int $sectionIndex,
        public string $content,
        public string $category,
        public string $documentType,
        public int $priority,
        public string $status,
        public string $sourceFile,
        public string $sourceSha256,
        public array $policyRelations,
    ) {}
}
