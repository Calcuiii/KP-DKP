<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

use InvalidArgumentException;
use RuntimeException;

final class KnowledgeBasePolicyResolver
{
    /**
     * @param  array<int, KnowledgeBaseSearchResult>  $results
     * @return array<int, KnowledgeBaseSearchResult>
     */
    public function resolve(string $topic, array $results): array
    {
        $topic = trim($topic);

        if ($topic === '') {
            throw new InvalidArgumentException('Topic must not be empty.');
        }

        foreach ($results as $index => $result) {
            if (! $result instanceof KnowledgeBaseSearchResult) {
                throw new RuntimeException(
                    sprintf('Knowledge base search result at index %d has invalid type.', $index),
                );
            }
        }

        if ($results === []) {
            return [];
        }

        $documentIds = [];

        foreach ($results as $result) {
            $documentIds[$result->chunk->documentId] = true;
        }

        $overriddenDocumentIds = [];
        $clarifiedDocumentIdsBySource = [];

        foreach ($results as $result) {
            $sourceDocumentId = $result->chunk->documentId;

            foreach ($result->chunk->policyRelations as $relation) {
                if (! in_array($topic, $relation['topics'], true)) {
                    continue;
                }

                $targetDocumentId = $relation['document_id'];

                if (! isset($documentIds[$targetDocumentId])) {
                    continue;
                }

                if ($relation['relation'] === 'overrides') {
                    $overriddenDocumentIds[$targetDocumentId] = true;

                    continue;
                }

                if ($relation['relation'] === 'clarifies') {
                    $clarifiedDocumentIdsBySource[$sourceDocumentId][$targetDocumentId] = true;
                }
            }
        }

        $resolved = array_values(array_filter(
            $results,
            static fn (KnowledgeBaseSearchResult $result): bool => ! isset(
                $overriddenDocumentIds[$result->chunk->documentId],
            ),
        ));

        usort(
            $resolved,
            static function (
                KnowledgeBaseSearchResult $left,
                KnowledgeBaseSearchResult $right,
            ) use ($clarifiedDocumentIdsBySource): int {
                $leftDocumentId = $left->chunk->documentId;
                $rightDocumentId = $right->chunk->documentId;

                if (
                    isset(
                        $clarifiedDocumentIdsBySource[$leftDocumentId][$rightDocumentId],
                    )
                ) {
                    return -1;
                }

                if (
                    isset(
                        $clarifiedDocumentIdsBySource[$rightDocumentId][$leftDocumentId],
                    )
                ) {
                    return 1;
                }

                return $right->score <=> $left->score
                    ?: $left->chunk->priority <=> $right->chunk->priority
                    ?: strcmp($leftDocumentId, $rightDocumentId)
                    ?: $left->chunk->sectionIndex <=> $right->chunk->sectionIndex
                    ?: strcmp($left->chunk->chunkId, $right->chunk->chunkId);
            },
        );

        return $resolved;
    }
}
