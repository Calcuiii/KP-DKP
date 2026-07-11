<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

final readonly class LoadedKnowledgeBaseDocument
{
    /**
     * @param  array<int, array{
     *     relation: string,
     *     document_id: string,
     *     topics: array<int, string>
     * }>  $policyRelations
     */
    public function __construct(
        public string $documentId,
        public string $title,
        public string $category,
        public string $documentType,
        public ?string $effectiveDate,
        public int $priority,
        public string $status,
        public string $sourceFile,
        public string $processedFile,
        public string $sourceSha256,
        public array $policyRelations,
        public string $content,
    ) {}
}
