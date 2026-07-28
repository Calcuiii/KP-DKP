<?php

namespace App\Support;

use App\KnowledgeBase\KnowledgeBaseRegistry;
use Illuminate\Support\Collection;

class KnowledgeBaseCategoryResolver
{
    protected Collection $map;

    public function __construct(KnowledgeBaseRegistry $registry)
    {
        $this->map = collect($registry->all())
            ->keyBy(fn ($doc) => $doc->documentId)
            ->map(fn ($doc) => $doc->category);
    }

    public function categoryFor(?string $documentId): string
    {
        if (! $documentId) {
            return 'Umum';
        }

        return $this->map->get($documentId, 'Umum');
    }
}