<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseDocument;
use App\KnowledgeBase\KnowledgeBaseDocumentLoader;
use App\KnowledgeBase\KnowledgeBaseRegistry;
use App\KnowledgeBase\LoadedKnowledgeBaseDocument;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class KnowledgeBaseDocumentLoaderTest extends TestCase
{
    private string $registryPath;

    private string $processedDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $projectRoot = dirname(__DIR__, 2);

        $this->registryPath = $projectRoot
            .'/storage/app/knowledge-base/metadata/documents.json';

        $this->processedDirectory = $projectRoot
            .'/storage/app/knowledge-base/processed';
    }

    public function test_it_loads_all_processed_documents_with_content(): void
    {
        $documents = (new KnowledgeBaseRegistry($this->registryPath))->all();

        $loadedDocuments = (new KnowledgeBaseDocumentLoader(
            $this->processedDirectory,
        ))->loadAll($documents);

        self::assertCount(10, $loadedDocuments);
        self::assertContainsOnlyInstancesOf(
            LoadedKnowledgeBaseDocument::class,
            $loadedDocuments,
        );

        foreach ($loadedDocuments as $loadedDocument) {
            self::assertNotSame('', trim($loadedDocument->content));
            self::assertStringStartsWith('# ', $loadedDocument->content);
            self::assertStringNotContainsString(
                'document_id:',
                $loadedDocument->content,
            );
        }
    }

    public function test_it_preserves_registry_metadata_and_policy_relations(): void
    {
        $documents = (new KnowledgeBaseRegistry($this->registryPath))->all();

        $loadedDocuments = (new KnowledgeBaseDocumentLoader(
            $this->processedDirectory,
        ))->loadAll($documents);

        $documentsById = [];

        foreach ($loadedDocuments as $document) {
            $documentsById[$document->documentId] = $document;
        }

        self::assertSame(
            'Kebijakan Surat Keterangan Magang, PKL, dan Penelitian',
            $documentsById['KB-008']->title,
        );

        self::assertSame(1, $documentsById['KB-008']->priority);

        self::assertSame(
            [
                [
                    'relation' => 'overrides',
                    'document_id' => 'KB-002',
                    'topics' => ['surat_keterangan'],
                ],
                [
                    'relation' => 'overrides',
                    'document_id' => 'KB-007',
                    'topics' => ['surat_keterangan'],
                ],
                [
                    'relation' => 'clarifies',
                    'document_id' => 'KB-002',
                    'topics' => ['sertifikat'],
                ],
                [
                    'relation' => 'clarifies',
                    'document_id' => 'KB-007',
                    'topics' => ['sertifikat'],
                ],
                [
                    'relation' => 'clarifies',
                    'document_id' => 'KB-010',
                    'topics' => ['sertifikat'],
                ],
            ],
            $documentsById['KB-008']->policyRelations,
        );
    }

    public function test_it_rejects_missing_processed_document(): void
    {
        $document = $this->validDocument([
            'processedFile' => 'missing-document.md',
        ]);

        $loader = new KnowledgeBaseDocumentLoader($this->processedDirectory);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Processed knowledge base document not found:');

        $loader->load($document);
    }

    public function test_it_rejects_front_matter_mismatch(): void
    {
        $directory = $this->createTemporaryDirectory();
        $path = $directory.'/test.md';

        file_put_contents(
            $path,
            <<<'MARKDOWN'
---
document_id: KB-WRONG
title: Test Document
category: test
document_type: official_infographic
effective_date: null
priority: 3
status: active
source_file: originals/images/test.jpeg
---

# Test Document

Content.
MARKDOWN,
        );

        $document = $this->validDocument();

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage(
                'Front matter field document_id does not match registry for KB-TEST.',
            );

            (new KnowledgeBaseDocumentLoader($directory))->load($document);
        } finally {
            @unlink($path);
            @rmdir($directory);
        }
    }

    public function test_it_rejects_invalid_document_type_in_load_all(): void
    {
        $loader = new KnowledgeBaseDocumentLoader($this->processedDirectory);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Knowledge base document at index 0 has invalid type.',
        );

        $loader->loadAll([
            'invalid',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function validDocument(array $overrides = []): KnowledgeBaseDocument
    {
        $values = array_replace([
            'documentId' => 'KB-TEST',
            'title' => 'Test Document',
            'category' => 'test',
            'documentType' => 'official_infographic',
            'effectiveDate' => null,
            'priority' => 3,
            'status' => 'active',
            'sourceFile' => 'originals/images/test.jpeg',
            'processedFile' => 'test.md',
            'sourceSha256' => str_repeat('a', 64),
            'policyRelations' => [],
        ], $overrides);

        return new KnowledgeBaseDocument(...$values);
    }

    private function createTemporaryDirectory(): string
    {
        $path = sys_get_temp_dir().'/kb-loader-'.bin2hex(random_bytes(8));

        self::assertTrue(mkdir($path));

        return $path;
    }
}
