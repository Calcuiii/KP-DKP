<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

final readonly class KnowledgeBaseSearchResult
{
    public function __construct(
        public KnowledgeBaseChunk $chunk,
        public int $score,
    ) {}
}
