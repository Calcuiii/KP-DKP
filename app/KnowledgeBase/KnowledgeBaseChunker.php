<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

use RuntimeException;

final class KnowledgeBaseChunker
{
    /**
     * @return array<int, KnowledgeBaseChunk>
     */
    public function chunk(LoadedKnowledgeBaseDocument $document): array
    {
        $sections = $this->extractSections($document);

        $chunks = [];

        foreach ($sections as $index => $section) {
            $sectionIndex = $index + 1;

            $chunks[] = new KnowledgeBaseChunk(
                chunkId: sprintf(
                    '%s::section-%03d',
                    $document->documentId,
                    $sectionIndex,
                ),
                documentId: $document->documentId,
                documentTitle: $document->title,
                sectionTitle: $section['title'],
                sectionIndex: $sectionIndex,
                content: $section['content'],
                category: $document->category,
                documentType: $document->documentType,
                priority: $document->priority,
                status: $document->status,
                sourceFile: $document->sourceFile,
                sourceSha256: $document->sourceSha256,
                policyRelations: $document->policyRelations,
            );
        }

        return $chunks;
    }

    /**
     * @param  array<int, LoadedKnowledgeBaseDocument>  $documents
     * @return array<int, KnowledgeBaseChunk>
     */
    public function chunkAll(array $documents): array
    {
        $chunks = [];

        foreach ($documents as $index => $document) {
            if (! $document instanceof LoadedKnowledgeBaseDocument) {
                throw new RuntimeException(
                    sprintf(
                        'Loaded knowledge base document at index %d has invalid type.',
                        $index,
                    ),
                );
            }

            array_push($chunks, ...$this->chunk($document));
        }

        return $chunks;
    }

    /**
     * @return array<int, array{title: string, content: string}>
     */
    private function extractSections(
        LoadedKnowledgeBaseDocument $document,
    ): array {
        $content = str_replace(["\r\n", "\r"], "\n", $document->content);

        $lines = explode("\n", $content);

        if ($lines === [] || ! str_starts_with(trim($lines[0]), '# ')) {
            throw new RuntimeException(
                sprintf(
                    'Knowledge base document %s must start with a level-one heading.',
                    $document->documentId,
                ),
            );
        }

        $sections = [];
        $currentTitle = null;
        $currentLines = [];

        foreach ($lines as $line) {
            if (str_starts_with($line, '## ')) {
                if ($currentTitle !== null) {
                    $sections[] = $this->buildSection(
                        $currentTitle,
                        $currentLines,
                        $document,
                    );
                }

                $currentTitle = trim(substr($line, 3));
                $currentLines = [$line];

                continue;
            }

            if ($currentTitle !== null) {
                $currentLines[] = $line;
            }
        }

        if ($currentTitle !== null) {
            $sections[] = $this->buildSection(
                $currentTitle,
                $currentLines,
                $document,
            );
        }

        if ($sections === []) {
            throw new RuntimeException(
                sprintf(
                    'Knowledge base document %s must contain at least one level-two section.',
                    $document->documentId,
                ),
            );
        }

        return $sections;
    }

    /**
     * @param  array<int, string>  $lines
     * @return array{title: string, content: string}
     */
    private function buildSection(
        string $title,
        array $lines,
        LoadedKnowledgeBaseDocument $document,
    ): array {
        if ($title === '') {
            throw new RuntimeException(
                sprintf(
                    'Knowledge base document %s contains an empty section title.',
                    $document->documentId,
                ),
            );
        }

        $content = trim(implode("\n", $lines));

        if ($content === '' || $content === '## '.$title) {
            throw new RuntimeException(
                sprintf(
                    'Knowledge base document %s contains an empty section: %s.',
                    $document->documentId,
                    $title,
                ),
            );
        }

        return [
            'title' => $title,
            'content' => $content,
        ];
    }
}
