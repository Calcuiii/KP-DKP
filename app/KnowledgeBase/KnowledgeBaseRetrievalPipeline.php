<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

use InvalidArgumentException;

final class KnowledgeBaseRetrievalPipeline
{
    public function __construct(
        private readonly KnowledgeBaseRegistry $registry,
        private readonly KnowledgeBaseDocumentLoader $documentLoader,
        private readonly KnowledgeBaseChunker $chunker,
        private readonly LexicalKnowledgeBaseRetriever $retriever,
        private readonly KnowledgeBasePolicyResolver $policyResolver,
    ) {}

    /**
     * @return array<int, KnowledgeBaseSearchResult>
     */
    public function retrieve(
        string $query,
        string $topic,
        int $topK = 5,
    ): array {
        if ($topK < 1) {
            throw new InvalidArgumentException('topK must be greater than zero.');
        }

        if (trim($query) === '') {
            throw new InvalidArgumentException('Query must not be empty.');
        }

        if (trim($topic) === '') {
            throw new InvalidArgumentException('Topic must not be empty.');
        }

        $documents = $this->registry->all();

        $loadedDocuments = $this->documentLoader->loadAll($documents);

        $chunks = $this->chunker->chunkAll($loadedDocuments);

        if ($chunks === []) {
            return [];
        }

        $retrieved = $this->retriever->retrieve(
            $query,
            $chunks,
            count($chunks),
        );

        $resolved = $this->policyResolver->resolve(
            $topic,
            $retrieved,
        );

        return array_slice($resolved, 0, $topK);
    }
}
