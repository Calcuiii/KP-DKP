<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseChunker;
use App\KnowledgeBase\KnowledgeBaseDocumentLoader;
use App\Models\KnowledgeBaseDocument;
use App\Services\KnowledgeBaseDocumentIndexer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Tests\TestCase;
use ZipArchive;

final class KnowledgeBaseDocumentIndexerTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/knowledge-base-indexer-'.bin2hex(random_bytes(8));
        File::ensureDirectoryExists($this->directory);
        File::put($this->registryPath(), json_encode([
            'schema_version' => '1.0',
            'documents' => [],
        ], JSON_THROW_ON_ERROR));

        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    public function test_it_indexes_a_docx_upload_and_registers_it_for_retrieval(): void
    {
        $document = $this->document();
        $this->putDocx($document->file_path, 'Persyaratan magang mencakup surat permohonan dan KTM.');

        $chunksCount = $this->indexer()->index($document);
        $registry = json_decode(File::get($this->registryPath()), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(1, $chunksCount);
        self::assertSame('KB-UPLOAD-000042', $registry['documents'][0]['document_id']);
        self::assertSame('uploaded_document', $registry['documents'][0]['document_type']);
        self::assertFileExists($this->processedDirectory().'/kb-upload-000042.md');
        self::assertStringContainsString(
            'Persyaratan magang mencakup surat permohonan dan KTM.',
            File::get($this->processedDirectory().'/kb-upload-000042.md'),
        );
    }

    public function test_it_replaces_the_previous_registry_entry_when_reindexing(): void
    {
        $document = $this->document();
        $this->putDocx($document->file_path, 'Versi pertama.');
        $this->indexer()->index($document);

        // A replacement upload has a new storage path, as in the application;
        // do not overwrite an archive that Windows may still be scanning.
        $document->file_path = 'knowledge-base/panduan-revised-'.bin2hex(random_bytes(8)).'.docx';
        $this->putDocx($document->file_path, 'Versi kedua.');
        $this->indexer()->index($document);
        $registry = json_decode(File::get($this->registryPath()), true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(1, $registry['documents']);
        self::assertStringContainsString('Versi kedua.', File::get(
            $this->processedDirectory().'/kb-upload-000042.md',
        ));
    }

    public function test_removed_and_failed_reindexed_documents_are_no_longer_registered(): void
    {
        $document = $this->document();
        $this->putDocx($document->file_path, 'Pedoman riset laut yang dapat dicabut.');
        $this->indexer()->index($document);
        $this->indexer()->remove($document);
        self::assertSame([], json_decode(File::get($this->registryPath()), true)['documents']);
        self::assertFileDoesNotExist($this->processedDirectory().'/kb-upload-000042.md');

        $this->indexer()->index($document);
        Storage::disk('local')->delete($document->file_path);
        try {
            $this->indexer()->index($document);
            self::fail('Missing file must fail reindexing.');
        } catch (\RuntimeException $exception) {
            self::assertSame([], json_decode(File::get($this->registryPath()), true)['documents']);
            self::assertFileDoesNotExist($this->processedDirectory().'/kb-upload-000042.md');
        }
    }

    private function indexer(): KnowledgeBaseDocumentIndexer
    {
        return new KnowledgeBaseDocumentIndexer(
            registryPath: $this->registryPath(),
            processedDirectory: $this->processedDirectory(),
            documentLoader: new KnowledgeBaseDocumentLoader($this->processedDirectory()),
            chunker: new KnowledgeBaseChunker,
            pdfParser: new Parser,
        );
    }

    private function document(): KnowledgeBaseDocument
    {
        $document = new KnowledgeBaseDocument([
            'name' => 'Panduan Magang',
            'category' => 'Panduan',
            'type' => 'DOCX',
            'file_path' => 'knowledge-base/panduan.docx',
        ]);
        $document->setAttribute('id', 42);

        return $document;
    }

    private function putDocx(string $path, string $content): void
    {
        $archive = new ZipArchive;
        $absolutePath = Storage::disk('local')->path($path);
        File::ensureDirectoryExists(dirname($absolutePath));

        self::assertTrue($archive->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $archive->addFromString(
            'word/document.xml',
            '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>'
            .htmlspecialchars($content, ENT_XML1, 'UTF-8')
            .'</w:t></w:r></w:p></w:body></w:document>',
        );
        $archive->close();
    }

    private function registryPath(): string
    {
        return $this->directory.'/documents.json';
    }

    private function processedDirectory(): string
    {
        return $this->directory.'/processed';
    }
}
