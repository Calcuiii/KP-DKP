<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseDocument;
use App\KnowledgeBase\KnowledgeBaseRegistry;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class KnowledgeBaseRegistryTest extends TestCase
{
    private string $registryPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registryPath = dirname(__DIR__, 2)
            .'/storage/app/knowledge-base/metadata/documents.json';
    }

    public function test_it_loads_all_registered_knowledge_base_documents(): void
    {
        $documents = (new KnowledgeBaseRegistry($this->registryPath))->all();

        self::assertCount(10, $documents);
        self::assertContainsOnlyInstancesOf(KnowledgeBaseDocument::class, $documents);

        self::assertSame(
            [
                'KB-001',
                'KB-002',
                'KB-003',
                'KB-004',
                'KB-005',
                'KB-006',
                'KB-007',
                'KB-008',
                'KB-009',
                'KB-010',
            ],
            array_map(
                static fn (KnowledgeBaseDocument $document): string => $document->documentId,
                $documents,
            ),
        );
    }

    public function test_it_preserves_registry_metadata_and_policy_relations(): void
    {
        $documents = (new KnowledgeBaseRegistry($this->registryPath))->all();

        $documentsById = [];

        foreach ($documents as $document) {
            $documentsById[$document->documentId] = $document;
        }

        self::assertSame(1, $documentsById['KB-008']->priority);
        self::assertSame('active', $documentsById['KB-008']->status);

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

        self::assertSame(4, $documentsById['KB-010']->priority);
        self::assertSame('reference', $documentsById['KB-010']->status);
        self::assertSame([], $documentsById['KB-010']->policyRelations);
    }

    public function test_it_rejects_duplicate_document_ids(): void
    {
        $registryPath = $this->createTemporaryRegistry([
            'documents' => [
                $this->validDocument(['document_id' => 'KB-001']),
                $this->validDocument(['document_id' => 'KB-001']),
            ],
        ]);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Duplicate knowledge base document ID: KB-001.');

            (new KnowledgeBaseRegistry($registryPath))->all();
        } finally {
            @unlink($registryPath);
        }
    }

    public function test_it_rejects_invalid_policy_relations(): void
    {
        $registryPath = $this->createTemporaryRegistry([
            'documents' => [
                $this->validDocument([
                    'policy_relations' => [
                        [
                            'relation' => 'supersedes',
                            'document_id' => 'KB-002',
                            'topics' => ['surat_keterangan'],
                        ],
                    ],
                ]),
            ],
        ]);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('has invalid relation.');

            (new KnowledgeBaseRegistry($registryPath))->all();
        } finally {
            @unlink($registryPath);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validDocument(array $overrides = []): array
    {
        return array_replace([
            'document_id' => 'KB-TEST',
            'title' => 'Test Document',
            'category' => 'test',
            'document_type' => 'official_infographic',
            'effective_date' => null,
            'priority' => 3,
            'status' => 'active',
            'source_file' => 'originals/images/test.jpeg',
            'processed_file' => 'test.md',
            'source_sha256' => str_repeat('a', 64),
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $registry
     */
    private function createTemporaryRegistry(array $registry): string
    {
        $path = tempnam(sys_get_temp_dir(), 'kb-registry-');

        self::assertNotFalse($path);

        $encoded = json_encode($registry, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        self::assertNotFalse(file_put_contents($path, $encoded));

        return $path;
    }
}
