<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

final readonly class KnowledgeBaseGroundedContext
{
    /**
     * @param  array<int, array{
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
     * }>  $sources
     */
    public function __construct(
        public string $query,
        public array $sources,
    ) {}
}
