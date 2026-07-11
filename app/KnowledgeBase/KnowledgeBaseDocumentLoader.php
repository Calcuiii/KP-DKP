<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

use JsonException;
use RuntimeException;

final class KnowledgeBaseDocumentLoader
{
    public function __construct(
        private readonly string $processedDirectory,
    ) {}

    public function load(KnowledgeBaseDocument $document): LoadedKnowledgeBaseDocument
    {
        $path = $this->processedPath($document);

        if (! is_file($path)) {
            throw new RuntimeException(
                sprintf('Processed knowledge base document not found: %s.', $path),
            );
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(
                sprintf('Unable to read processed knowledge base document: %s.', $path),
            );
        }

        [$frontMatter, $content] = $this->parseDocument($contents, $path);

        $this->validateFrontMatter($document, $frontMatter, $path);

        return new LoadedKnowledgeBaseDocument(
            documentId: $document->documentId,
            title: $document->title,
            category: $document->category,
            documentType: $document->documentType,
            effectiveDate: $document->effectiveDate,
            priority: $document->priority,
            status: $document->status,
            sourceFile: $document->sourceFile,
            processedFile: $document->processedFile,
            sourceSha256: $document->sourceSha256,
            policyRelations: $document->policyRelations,
            content: $content,
        );
    }

    /**
     * @param  array<int, KnowledgeBaseDocument>  $documents
     * @return array<int, LoadedKnowledgeBaseDocument>
     */
    public function loadAll(array $documents): array
    {
        $loadedDocuments = [];

        foreach ($documents as $index => $document) {
            if (! $document instanceof KnowledgeBaseDocument) {
                throw new RuntimeException(
                    sprintf('Knowledge base document at index %d has invalid type.', $index),
                );
            }

            $loadedDocuments[] = $this->load($document);
        }

        return $loadedDocuments;
    }

    private function processedPath(KnowledgeBaseDocument $document): string
    {
        return rtrim($this->processedDirectory, DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .$document->processedFile;
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function parseDocument(string $contents, string $path): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $contents);

        if (! str_starts_with($normalized, "---\n")) {
            throw new RuntimeException(
                sprintf('Processed knowledge base document has no front matter: %s.', $path),
            );
        }

        $closingDelimiterPosition = strpos($normalized, "\n---\n", 4);

        if ($closingDelimiterPosition === false) {
            throw new RuntimeException(
                sprintf('Processed knowledge base document has invalid front matter: %s.', $path),
            );
        }

        $frontMatterText = substr(
            $normalized,
            4,
            $closingDelimiterPosition - 4,
        );

        $content = trim(
            substr($normalized, $closingDelimiterPosition + 5),
        );

        if ($content === '') {
            throw new RuntimeException(
                sprintf('Processed knowledge base document has empty content: %s.', $path),
            );
        }

        return [
            $this->parseFrontMatter($frontMatterText, $path),
            $content,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseFrontMatter(string $frontMatterText, string $path): array
    {
        $frontMatter = [];

        foreach (explode("\n", $frontMatterText) as $lineNumber => $line) {
            if (trim($line) === '') {
                continue;
            }

            if (! str_contains($line, ':')) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid front matter line %d in %s.',
                        $lineNumber + 1,
                        $path,
                    ),
                );
            }

            [$key, $rawValue] = explode(':', $line, 2);

            $key = trim($key);
            $rawValue = trim($rawValue);

            if ($key === '') {
                throw new RuntimeException(
                    sprintf(
                        'Invalid front matter key on line %d in %s.',
                        $lineNumber + 1,
                        $path,
                    ),
                );
            }

            if (array_key_exists($key, $frontMatter)) {
                throw new RuntimeException(
                    sprintf('Duplicate front matter key %s in %s.', $key, $path),
                );
            }

            $frontMatter[$key] = $this->parseFrontMatterValue(
                $rawValue,
                $key,
                $path,
            );
        }

        return $frontMatter;
    }

    private function parseFrontMatterValue(
        string $rawValue,
        string $key,
        string $path,
    ): mixed {
        if ($rawValue === 'null') {
            return null;
        }

        if (preg_match('/^-?\d+$/', $rawValue) === 1) {
            return (int) $rawValue;
        }

        if (str_starts_with($rawValue, '[') || str_starts_with($rawValue, '{')) {
            try {
                return json_decode($rawValue, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new RuntimeException(
                    sprintf('Invalid JSON front matter value for %s in %s.', $key, $path),
                    previous: $exception,
                );
            }
        }

        return $rawValue;
    }

    /**
     * @param  array<string, mixed>  $frontMatter
     */
    private function validateFrontMatter(
        KnowledgeBaseDocument $document,
        array $frontMatter,
        string $path,
    ): void {
        $expected = [
            'document_id' => $document->documentId,
            'title' => $document->title,
            'category' => $document->category,
            'document_type' => $document->documentType,
            'effective_date' => $document->effectiveDate,
            'priority' => $document->priority,
            'status' => $document->status,
            'source_file' => $document->sourceFile,
            'policy_relations' => $document->policyRelations,
        ];

        foreach ($expected as $field => $expectedValue) {
            if ($field === 'policy_relations' && ! array_key_exists($field, $frontMatter)) {
                $actualValue = [];
            } else {
                if (! array_key_exists($field, $frontMatter)) {
                    throw new RuntimeException(
                        sprintf('Missing front matter field %s in %s.', $field, $path),
                    );
                }

                $actualValue = $frontMatter[$field];
            }

            if ($actualValue !== $expectedValue) {
                throw new RuntimeException(
                    sprintf(
                        'Front matter field %s does not match registry for %s.',
                        $field,
                        $document->documentId,
                    ),
                );
            }
        }
    }
}
