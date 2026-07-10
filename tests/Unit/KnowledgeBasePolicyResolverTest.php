<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseChunk;
use App\KnowledgeBase\KnowledgeBasePolicyResolver;
use App\KnowledgeBase\KnowledgeBaseSearchResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class KnowledgeBasePolicyResolverTest extends TestCase
{
    public function test_it_removes_overridden_documents_for_matching_topic(): void
    {
        $resolver = new KnowledgeBasePolicyResolver;

        $results = [
            $this->searchResult('KB-002', 20),
            $this->searchResult('KB-008', 10, [
                [
                    'relation' => 'overrides',
                    'document_id' => 'KB-002',
                    'topics' => ['surat_keterangan'],
                ],
            ]),
        ];

        $resolved = $resolver->resolve('surat_keterangan', $results);

        self::assertSame(
            ['KB-008'],
            $this->documentIds($resolved),
        );
    }

    public function test_it_does_not_apply_override_for_unmatched_topic(): void
    {
        $resolver = new KnowledgeBasePolicyResolver;

        $results = [
            $this->searchResult('KB-002', 20),
            $this->searchResult('KB-008', 10, [
                [
                    'relation' => 'overrides',
                    'document_id' => 'KB-002',
                    'topics' => ['surat_keterangan'],
                ],
            ]),
        ];

        $resolved = $resolver->resolve('sertifikat', $results);

        self::assertSame(
            ['KB-002', 'KB-008'],
            $this->documentIds($resolved),
        );
    }

    public function test_it_prioritizes_clarifying_document_before_target(): void
    {
        $resolver = new KnowledgeBasePolicyResolver;

        $results = [
            $this->searchResult('KB-002', 20),
            $this->searchResult('KB-008', 10, [
                [
                    'relation' => 'clarifies',
                    'document_id' => 'KB-002',
                    'topics' => ['sertifikat'],
                ],
            ]),
        ];

        $resolved = $resolver->resolve('sertifikat', $results);

        self::assertSame(
            ['KB-008', 'KB-002'],
            $this->documentIds($resolved),
        );
    }

    public function test_it_preserves_unrelated_results(): void
    {
        $resolver = new KnowledgeBasePolicyResolver;

        $results = [
            $this->searchResult('KB-001', 30),
            $this->searchResult('KB-002', 20),
            $this->searchResult('KB-008', 10, [
                [
                    'relation' => 'overrides',
                    'document_id' => 'KB-002',
                    'topics' => ['surat_keterangan'],
                ],
            ]),
        ];

        $resolved = $resolver->resolve('surat_keterangan', $results);

        self::assertSame(
            ['KB-001', 'KB-008'],
            $this->documentIds($resolved),
        );
    }

    public function test_it_does_not_apply_relation_when_target_is_absent(): void
    {
        $resolver = new KnowledgeBasePolicyResolver;

        $results = [
            $this->searchResult('KB-001', 30),
            $this->searchResult('KB-008', 10, [
                [
                    'relation' => 'overrides',
                    'document_id' => 'KB-002',
                    'topics' => ['surat_keterangan'],
                ],
            ]),
        ];

        $resolved = $resolver->resolve('surat_keterangan', $results);

        self::assertSame(
            ['KB-001', 'KB-008'],
            $this->documentIds($resolved),
        );
    }

    public function test_it_preserves_scores(): void
    {
        $resolver = new KnowledgeBasePolicyResolver;

        $results = [
            $this->searchResult('KB-002', 20),
            $this->searchResult('KB-008', 10, [
                [
                    'relation' => 'clarifies',
                    'document_id' => 'KB-002',
                    'topics' => ['sertifikat'],
                ],
            ]),
        ];

        $resolved = $resolver->resolve('sertifikat', $results);

        self::assertSame(10, $resolved[0]->score);
        self::assertSame(20, $resolved[1]->score);
    }

    public function test_it_uses_deterministic_fallback_ordering(): void
    {
        $resolver = new KnowledgeBasePolicyResolver;

        $results = [
            $this->searchResult('KB-003', 10, [], priority: 3, sectionIndex: 2),
            $this->searchResult('KB-002', 10, [], priority: 2, sectionIndex: 1),
            $this->searchResult('KB-001', 20),
        ];

        $resolved = $resolver->resolve('unrelated_topic', $results);

        self::assertSame(
            ['KB-001', 'KB-002', 'KB-003'],
            $this->documentIds($resolved),
        );
    }

    public function test_it_returns_empty_results_unchanged(): void
    {
        self::assertSame(
            [],
            (new KnowledgeBasePolicyResolver)->resolve('sertifikat', []),
        );
    }

    public function test_it_rejects_empty_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Topic must not be empty.');

        (new KnowledgeBasePolicyResolver)->resolve('   ', []);
    }

    public function test_it_rejects_invalid_search_result_type(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Knowledge base search result at index 0 has invalid type.',
        );

        (new KnowledgeBasePolicyResolver)->resolve(
            'sertifikat',
            ['invalid'],
        );
    }

    /**
     * @param  array<int, array{
     *     relation: string,
     *     document_id: string,
     *     topics: array<int, string>
     * }>  $policyRelations
     */
    private function searchResult(
        string $documentId,
        int $score,
        array $policyRelations = [],
        int $priority = 3,
        int $sectionIndex = 1,
    ): KnowledgeBaseSearchResult {
        return new KnowledgeBaseSearchResult(
            chunk: new KnowledgeBaseChunk(
                chunkId: sprintf(
                    '%s::section-%03d',
                    $documentId,
                    $sectionIndex,
                ),
                documentId: $documentId,
                documentTitle: $documentId,
                sectionTitle: 'Test Section',
                sectionIndex: $sectionIndex,
                content: 'Test content.',
                category: 'test',
                documentType: 'official_infographic',
                priority: $priority,
                status: 'active',
                sourceFile: 'test.jpeg',
                sourceSha256: str_repeat('a', 64),
                policyRelations: $policyRelations,
            ),
            score: $score,
        );
    }

    /**
     * @param  array<int, KnowledgeBaseSearchResult>  $results
     * @return array<int, string>
     */
    private function documentIds(array $results): array
    {
        return array_map(
            static fn (KnowledgeBaseSearchResult $result): string => $result->chunk->documentId,
            $results,
        );
    }
}
