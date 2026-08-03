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
        private readonly KnowledgeBaseTopicResolver $topicResolver,
    ) {}

    /**
     * @return array<int, KnowledgeBaseSearchResult>
     */
    public function retrieve(
        string $query,
        int $topK = 5,
    ): array {
        if ($topK < 1) {
            throw new InvalidArgumentException('topK must be greater than zero.');
        }

        if (trim($query) === '') {
            throw new InvalidArgumentException('Query must not be empty.');
        }

        return array_slice($this->retrieveAll($query), 0, $topK);
    }

    /**
     * @return array<int, KnowledgeBaseSearchResult>
     */
    public function retrieveAll(string $query): array
    {
        if (trim($query) === '') {
            throw new InvalidArgumentException('Query must not be empty.');
        }

        $topic = $this->topicResolver->resolve($query);
        $documents = $this->registry->all();
        $loadedDocuments = $this->documentLoader->loadAll($documents);
        $chunks = $this->chunker->chunkAll($loadedDocuments);

        if ($chunks === []) {
            return [];
        }

        $retrieved = $this->retriever->retrieve($query, $chunks, count($chunks));

        return $topic === null
            ? $retrieved
            : $this->policyResolver->resolve($topic, $retrieved);
    }
}
