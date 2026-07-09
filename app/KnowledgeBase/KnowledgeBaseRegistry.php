<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

use JsonException;
use RuntimeException;

final class KnowledgeBaseRegistry
{
    public function __construct(
        private readonly string $registryPath,
    ) {}

    /**
     * @return array<int, KnowledgeBaseDocument>
     */
    public function all(): array
    {
        $data = $this->readRegistry();

        if (! isset($data['documents']) || ! is_array($data['documents'])) {
            throw new RuntimeException('Knowledge base registry must contain a documents array.');
        }

        $documents = [];
        $documentIds = [];

        foreach ($data['documents'] as $index => $document) {
            if (! is_array($document)) {
                throw new RuntimeException(
                    sprintf('Knowledge base registry document at index %d must be an object.', $index),
                );
            }

            $mappedDocument = $this->mapDocument($document, $index);

            if (isset($documentIds[$mappedDocument->documentId])) {
                throw new RuntimeException(
                    sprintf('Duplicate knowledge base document ID: %s.', $mappedDocument->documentId),
                );
            }

            $documentIds[$mappedDocument->documentId] = true;
            $documents[] = $mappedDocument;
        }

        return $documents;
    }

    /**
     * @return array<string, mixed>
     */
    private function readRegistry(): array
    {
        if (! is_file($this->registryPath)) {
            throw new RuntimeException(
                sprintf('Knowledge base registry not found: %s.', $this->registryPath),
            );
        }

        $contents = file_get_contents($this->registryPath);

        if ($contents === false) {
            throw new RuntimeException(
                sprintf('Unable to read knowledge base registry: %s.', $this->registryPath),
            );
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                sprintf('Invalid knowledge base registry JSON: %s.', $exception->getMessage()),
                previous: $exception,
            );
        }

        if (! is_array($data)) {
            throw new RuntimeException('Knowledge base registry root must be an object.');
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $document
     */
    private function mapDocument(array $document, int $index): KnowledgeBaseDocument
    {
        $requiredStringFields = [
            'document_id',
            'title',
            'category',
            'document_type',
            'status',
            'source_file',
            'processed_file',
            'source_sha256',
        ];

        foreach ($requiredStringFields as $field) {
            if (
                ! array_key_exists($field, $document)
                || ! is_string($document[$field])
                || trim($document[$field]) === ''
            ) {
                throw new RuntimeException(
                    sprintf(
                        'Knowledge base registry document at index %d has invalid %s.',
                        $index,
                        $field,
                    ),
                );
            }
        }

        if (! isset($document['priority']) || ! is_int($document['priority'])) {
            throw new RuntimeException(
                sprintf('Knowledge base registry document at index %d has invalid priority.', $index),
            );
        }

        $effectiveDate = $document['effective_date'] ?? null;

        if ($effectiveDate !== null && ! is_string($effectiveDate)) {
            throw new RuntimeException(
                sprintf('Knowledge base registry document at index %d has invalid effective_date.', $index),
            );
        }

        $policyRelations = $document['policy_relations'] ?? [];

        if (! is_array($policyRelations)) {
            throw new RuntimeException(
                sprintf('Knowledge base registry document at index %d has invalid policy_relations.', $index),
            );
        }

        $policyRelations = $this->validatePolicyRelations($policyRelations, $index);

        return new KnowledgeBaseDocument(
            documentId: $document['document_id'],
            title: $document['title'],
            category: $document['category'],
            documentType: $document['document_type'],
            effectiveDate: $effectiveDate,
            priority: $document['priority'],
            status: $document['status'],
            sourceFile: $document['source_file'],
            processedFile: $document['processed_file'],
            sourceSha256: $document['source_sha256'],
            policyRelations: $policyRelations,
        );
    }

    /**
     * @param  array<mixed>  $policyRelations
     * @return array<int, array{
     *     relation: string,
     *     document_id: string,
     *     topics: array<int, string>
     * }>
     */
    private function validatePolicyRelations(array $policyRelations, int $documentIndex): array
    {
        $validated = [];

        foreach ($policyRelations as $relationIndex => $policyRelation) {
            if (! is_array($policyRelation)) {
                throw new RuntimeException(
                    sprintf(
                        'Policy relation %d for document at index %d must be an object.',
                        $relationIndex,
                        $documentIndex,
                    ),
                );
            }

            $relation = $policyRelation['relation'] ?? null;
            $targetDocumentId = $policyRelation['document_id'] ?? null;
            $topics = $policyRelation['topics'] ?? null;

            if (! is_string($relation) || ! in_array($relation, ['overrides', 'clarifies'], true)) {
                throw new RuntimeException(
                    sprintf(
                        'Policy relation %d for document at index %d has invalid relation.',
                        $relationIndex,
                        $documentIndex,
                    ),
                );
            }

            if (! is_string($targetDocumentId) || trim($targetDocumentId) === '') {
                throw new RuntimeException(
                    sprintf(
                        'Policy relation %d for document at index %d has invalid document_id.',
                        $relationIndex,
                        $documentIndex,
                    ),
                );
            }

            if (! is_array($topics) || $topics === []) {
                throw new RuntimeException(
                    sprintf(
                        'Policy relation %d for document at index %d must contain topics.',
                        $relationIndex,
                        $documentIndex,
                    ),
                );
            }

            foreach ($topics as $topic) {
                if (! is_string($topic) || trim($topic) === '') {
                    throw new RuntimeException(
                        sprintf(
                            'Policy relation %d for document at index %d contains an invalid topic.',
                            $relationIndex,
                            $documentIndex,
                        ),
                    );
                }
            }

            $validated[] = [
                'relation' => $relation,
                'document_id' => $targetDocumentId,
                'topics' => array_values($topics),
            ];
        }

        return $validated;
    }
}
