<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseChunk;
use App\KnowledgeBase\KnowledgeBaseChunker;
use App\KnowledgeBase\KnowledgeBaseDocumentLoader;
use App\KnowledgeBase\KnowledgeBaseRegistry;
use App\KnowledgeBase\LoadedKnowledgeBaseDocument;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class KnowledgeBaseChunkerTest extends TestCase
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

    public function test_it_chunks_all_documents_by_level_two_sections(): void
    {
        $documents = (new KnowledgeBaseRegistry($this->registryPath))->all();

        $loadedDocuments = (new KnowledgeBaseDocumentLoader(
            $this->processedDirectory,
        ))->loadAll($documents);

        $chunks = (new KnowledgeBaseChunker)->chunkAll($loadedDocuments);

        self::assertCount(85, $chunks);
        self::assertContainsOnlyInstancesOf(KnowledgeBaseChunk::class, $chunks);

        $chunkIds = array_map(
            static fn (KnowledgeBaseChunk $chunk): string => $chunk->chunkId,
            $chunks,
        );

        self::assertCount(count($chunkIds), array_unique($chunkIds));

        foreach ($chunks as $chunk) {
            self::assertSame(
                sprintf(
                    '%s::section-%03d',
                    $chunk->documentId,
                    $chunk->sectionIndex,
                ),
                $chunk->chunkId,
            );

            self::assertNotSame('', trim($chunk->sectionTitle));
            self::assertStringStartsWith('## ', $chunk->content);
        }
    }

    public function test_it_preserves_document_metadata_and_policy_relations(): void
    {
        $documents = (new KnowledgeBaseRegistry($this->registryPath))->all();

        $loadedDocuments = (new KnowledgeBaseDocumentLoader(
            $this->processedDirectory,
        ))->loadAll($documents);

        $chunks = (new KnowledgeBaseChunker)->chunkAll($loadedDocuments);

        $policyChunk = null;

        foreach ($chunks as $chunk) {
            if (
                $chunk->documentId === 'KB-008'
                && $chunk->sectionTitle === 'Surat Keterangan'
            ) {
                $policyChunk = $chunk;

                break;
            }
        }

        self::assertInstanceOf(KnowledgeBaseChunk::class, $policyChunk);
        self::assertSame(1, $policyChunk->priority);
        self::assertSame('active', $policyChunk->status);
        self::assertSame(
            'official_circular',
            $policyChunk->documentType,
        );

        self::assertSame(
            $loadedDocuments[7]->policyRelations,
            $policyChunk->policyRelations,
        );
    }

    public function test_it_preserves_section_order_and_content(): void
    {
        $document = $this->loadedDocument(
            content: <<<'MARKDOWN'
# Test Document

## First Section

First content.

## Second Section

- Item one.
- Item two.
MARKDOWN,
        );

        $chunks = (new KnowledgeBaseChunker)->chunk($document);

        self::assertCount(2, $chunks);

        self::assertSame('KB-TEST::section-001', $chunks[0]->chunkId);
        self::assertSame('First Section', $chunks[0]->sectionTitle);
        self::assertSame(1, $chunks[0]->sectionIndex);
        self::assertSame(
            "## First Section\n\nFirst content.",
            $chunks[0]->content,
        );

        self::assertSame('KB-TEST::section-002', $chunks[1]->chunkId);
        self::assertSame('Second Section', $chunks[1]->sectionTitle);
        self::assertSame(2, $chunks[1]->sectionIndex);
        self::assertSame(
            "## Second Section\n\n- Item one.\n- Item two.",
            $chunks[1]->content,
        );
    }

    public function test_it_rejects_document_without_level_one_heading(): void
    {
        $document = $this->loadedDocument(
            content: <<<'MARKDOWN'
## Section

Content.
MARKDOWN,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Knowledge base document KB-TEST must start with a level-one heading.',
        );

        (new KnowledgeBaseChunker)->chunk($document);
    }

    public function test_it_rejects_document_without_level_two_sections(): void
    {
        $document = $this->loadedDocument(
            content: <<<'MARKDOWN'
# Test Document

Content without sections.
MARKDOWN,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Knowledge base document KB-TEST must contain at least one level-two section.',
        );

        (new KnowledgeBaseChunker)->chunk($document);
    }

    public function test_it_rejects_empty_section_content(): void
    {
        $document = $this->loadedDocument(
            content: <<<'MARKDOWN'
# Test Document

## Empty Section
MARKDOWN,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Knowledge base document KB-TEST contains an empty section: Empty Section.',
        );

        (new KnowledgeBaseChunker)->chunk($document);
    }

    public function test_it_rejects_invalid_document_type_in_chunk_all(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Loaded knowledge base document at index 0 has invalid type.',
        );

        (new KnowledgeBaseChunker)->chunkAll([
            'invalid',
        ]);
    }

    private function loadedDocument(string $content): LoadedKnowledgeBaseDocument
    {
        return new LoadedKnowledgeBaseDocument(
            documentId: 'KB-TEST',
            title: 'Test Document',
            category: 'test',
            documentType: 'official_infographic',
            effectiveDate: null,
            priority: 3,
            status: 'active',
            sourceFile: 'originals/images/test.jpeg',
            processedFile: 'test.md',
            sourceSha256: str_repeat('a', 64),
            policyRelations: [],
            content: $content,
        );
    }
}
