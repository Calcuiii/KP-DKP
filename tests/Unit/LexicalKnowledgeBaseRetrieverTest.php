<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseChunk;
use App\KnowledgeBase\KnowledgeBaseChunker;
use App\KnowledgeBase\KnowledgeBaseDocumentLoader;
use App\KnowledgeBase\KnowledgeBaseRegistry;
use App\KnowledgeBase\KnowledgeBaseSearchResult;
use App\KnowledgeBase\LexicalKnowledgeBaseRetriever;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class LexicalKnowledgeBaseRetrieverTest extends TestCase
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

    public function test_it_retrieves_relevant_chunks_for_certificate_query(): void
    {
        $results = $this->retrieveFromKnowledgeBase(
            'bagaimana penerbitan sertifikat',
            5,
        );

        self::assertNotEmpty($results);
        self::assertContainsOnlyInstancesOf(
            KnowledgeBaseSearchResult::class,
            $results,
        );
        self::assertLessThanOrEqual(5, count($results));

        self::assertTrue(
            array_any(
                $results,
                static fn (KnowledgeBaseSearchResult $result): bool => str_contains(
                    mb_strtolower($result->chunk->content),
                    'sertifikat',
                )
                    || str_contains(
                        mb_strtolower($result->chunk->sectionTitle),
                        'sertifikat',
                    ),
            ),
        );
    }

    public function test_it_returns_results_sorted_by_descending_score(): void
    {
        $results = $this->retrieveFromKnowledgeBase(
            'surat permohonan',
            10,
        );

        self::assertNotEmpty($results);

        $scores = array_map(
            static fn (KnowledgeBaseSearchResult $result): int => $result->score,
            $results,
        );

        $sortedScores = $scores;
        rsort($sortedScores);

        self::assertSame($sortedScores, $scores);
    }

    public function test_it_respects_top_k(): void
    {
        $results = $this->retrieveFromKnowledgeBase('magang', 3);

        self::assertCount(3, $results);
    }

    public function test_it_returns_no_results_for_unmatched_query(): void
    {
        $results = $this->retrieveFromKnowledgeBase(
            'xylophonequasarnebulazebra',
            5,
        );

        self::assertSame([], $results);
    }

    public function test_it_normalizes_case_and_punctuation(): void
    {
        $retriever = new LexicalKnowledgeBaseRetriever;

        $chunks = [
            $this->chunk(
                chunkId: 'KB-TEST-S001',
                sectionTitle: 'Penerbitan Sertifikat',
                content: 'Sertifikat dapat diproses melalui surat permohonan.',
            ),
        ];

        $plainResults = $retriever->retrieve(
            'penerbitan sertifikat',
            $chunks,
        );

        $normalizedResults = $retriever->retrieve(
            '  PENERBITAN, SERTIFIKAT!!!  ',
            $chunks,
        );

        self::assertSame(
            $plainResults[0]->score,
            $normalizedResults[0]->score,
        );
    }

    public function test_it_uses_stable_tie_breaking(): void
    {
        $retriever = new LexicalKnowledgeBaseRetriever;

        $chunks = [
            $this->chunk(
                chunkId: 'KB-002-S002',
                documentId: 'KB-002',
                sectionIndex: 2,
                priority: 3,
                content: 'alpha',
            ),
            $this->chunk(
                chunkId: 'KB-001-S002',
                documentId: 'KB-001',
                sectionIndex: 2,
                priority: 3,
                content: 'alpha',
            ),
            $this->chunk(
                chunkId: 'KB-003-S001',
                documentId: 'KB-003',
                sectionIndex: 1,
                priority: 1,
                content: 'alpha',
            ),
        ];

        $results = $retriever->retrieve('alpha', $chunks, 3);

        self::assertSame(
            [
                'KB-003-S001',
                'KB-001-S002',
                'KB-002-S002',
            ],
            array_map(
                static fn (KnowledgeBaseSearchResult $result): string => $result->chunk->chunkId,
                $results,
            ),
        );
    }

    public function test_it_rejects_empty_query(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query must not be empty.');

        (new LexicalKnowledgeBaseRetriever)->retrieve('   ', []);
    }

    public function test_it_rejects_invalid_top_k(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('topK must be greater than zero.');

        (new LexicalKnowledgeBaseRetriever)->retrieve('magang', [], 0);
    }

    public function test_it_rejects_invalid_chunk_type(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Knowledge base chunk at index 0 has invalid type.',
        );

        (new LexicalKnowledgeBaseRetriever)->retrieve(
            'magang',
            ['invalid'],
        );
    }

    public function test_it_returns_no_results_when_all_query_tokens_are_stopwords(): void
    {
        $retriever = new LexicalKnowledgeBaseRetriever;

        $chunks = [
            $this->chunk(
                chunkId: 'KB-TEST-S001',
                sectionTitle: 'Informasi Umum',
                content: 'Layanan magang disediakan oleh instansi terkait.',
            ),
        ];

        $results = $retriever->retrieve(
            'apa yang bagaimana',
            $chunks,
        );

        self::assertSame([], $results);
    }

    public function test_it_produces_identical_scores_for_stopword_and_informative_only_queries(): void
    {
        $retriever = new LexicalKnowledgeBaseRetriever;

        $chunks = [
            $this->chunk(
                chunkId: 'KB-TEST-S001',
                documentId: 'KB-TEST-A',
                sectionTitle: 'Penerbitan Dokumen',
                content: 'Formulir permohonan meliputi surat dan sertifikat resmi.',
            ),
            $this->chunk(
                chunkId: 'KB-TEST-S002',
                documentId: 'KB-TEST-B',
                sectionTitle: 'Surat Edaran',
                content: 'Sertifikat diterbitkan setelah proses verifikasi selesai.',
            ),
        ];

        $informativeOnlyResults = $retriever->retrieve(
            'surat sertifikat',
            $chunks,
            10,
        );

        $stopwordPrefixedResults = $retriever->retrieve(
            'apakah surat sertifikat',
            $chunks,
            10,
        );

        self::assertNotEmpty($informativeOnlyResults);

        self::assertCount(
            count($informativeOnlyResults),
            $stopwordPrefixedResults,
        );

        foreach ($informativeOnlyResults as $index => $result) {
            self::assertSame(
                $result->chunk->chunkId,
                $stopwordPrefixedResults[$index]->chunk->chunkId,
            );

            self::assertSame(
                $result->score,
                $stopwordPrefixedResults[$index]->score,
            );
        }
    }

    public function test_it_reduces_false_positive_results_for_real_corpus_regression_query(): void
    {
        $results = $this->retrieveFromKnowledgeBase(
            'query-yang-sama-sekali-tidak-ada-xyz',
            100,
        );

        self::assertLessThan(34, count($results));
        self::assertNotEmpty($results);

        foreach ($results as $result) {
            $haystack = mb_strtolower(
                $result->chunk->sectionTitle
                    .' '.$result->chunk->documentTitle
                    .' '.$result->chunk->content,
            );

            self::assertMatchesRegularExpression(
                '/\btidak\b/u',
                $haystack,
            );
        }
    }

    /**
     * @return array<int, KnowledgeBaseSearchResult>
     */
    private function retrieveFromKnowledgeBase(
        string $query,
        int $topK,
    ): array {
        $documents = (new KnowledgeBaseRegistry($this->registryPath))->all();

        $loadedDocuments = (new KnowledgeBaseDocumentLoader(
            $this->processedDirectory,
        ))->loadAll($documents);

        $chunks = (new KnowledgeBaseChunker)->chunkAll($loadedDocuments);

        return (new LexicalKnowledgeBaseRetriever)->retrieve(
            $query,
            $chunks,
            $topK,
        );
    }

    private function chunk(
        string $chunkId,
        string $documentId = 'KB-TEST',
        string $sectionTitle = 'Test Section',
        int $sectionIndex = 1,
        string $content = 'Test content',
        int $priority = 3,
    ): KnowledgeBaseChunk {
        return new KnowledgeBaseChunk(
            chunkId: $chunkId,
            documentId: $documentId,
            documentTitle: 'Test Document',
            sectionTitle: $sectionTitle,
            sectionIndex: $sectionIndex,
            content: $content,
            category: 'test',
            documentType: 'official_infographic',
            priority: $priority,
            status: 'active',
            sourceFile: 'originals/images/test.jpeg',
            sourceSha256: str_repeat('a', 64),
            policyRelations: [],
        );
    }
}
