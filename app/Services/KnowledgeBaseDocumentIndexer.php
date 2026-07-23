<?php

declare(strict_types=1);

namespace App\Services;

use App\KnowledgeBase\KnowledgeBaseChunker;
use App\KnowledgeBase\KnowledgeBaseDocument as RegistryDocument;
use App\KnowledgeBase\KnowledgeBaseDocumentLoader;
use App\Models\KnowledgeBaseDocument;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;
use Smalot\PdfParser\Parser;
use ZipArchive;

final class KnowledgeBaseDocumentIndexer
{
    public function __construct(
        private readonly string $registryPath,
        private readonly string $processedDirectory,
        private readonly KnowledgeBaseDocumentLoader $documentLoader,
        private readonly KnowledgeBaseChunker $chunker,
        private readonly Parser $pdfParser,
    ) {}

    public function index(KnowledgeBaseDocument $document): int
    {
        $sourcePath = Storage::disk('public')->path($document->file_path);

        if (! is_file($sourcePath)) {
            throw new RuntimeException('Berkas dokumen yang diunggah tidak ditemukan.');
        }

        $content = $this->extractText($sourcePath, $document->type);

        if ($content === '') {
            throw new RuntimeException('Tidak ada teks yang dapat dibaca dari dokumen ini.');
        }

        $registryDocument = $this->registryDocument($document, $sourcePath);
        $processedPath = rtrim($this->processedDirectory, DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.$registryDocument->processedFile;

        File::ensureDirectoryExists($this->processedDirectory);

        try {
            File::put($processedPath, $this->toMarkdown($registryDocument, $content));
            $loadedDocument = $this->documentLoader->load($registryDocument);
            $chunksCount = count($this->chunker->chunk($loadedDocument));
            $this->updateRegistry($registryDocument);
        } catch (\Throwable $exception) {
            File::delete($processedPath);

            throw $exception;
        }

        return $chunksCount;
    }

    private function extractText(string $path, string $type): string
    {
        return match (strtolower($type)) {
            'pdf' => $this->normalizeText($this->pdfParser->parseFile($path)->getText()),
            'docx' => $this->extractDocxText($path),
            'xlsx' => $this->extractXlsxText($path),
            default => throw new RuntimeException('Format dokumen tidak didukung untuk pengindeksan.'),
        };
    }

    private function extractDocxText(string $path): string
    {
        $archive = new ZipArchive;

        if ($archive->open($path) !== true) {
            throw new RuntimeException('Dokumen DOCX tidak dapat dibuka.');
        }

        try {
            $xml = $archive->getFromName('word/document.xml');
        } finally {
            $archive->close();
        }

        if (! is_string($xml)) {
            throw new RuntimeException('Isi dokumen DOCX tidak ditemukan.');
        }

        return $this->normalizeText(html_entity_decode(
            strip_tags(str_replace(['</w:p>', '</w:tr>'], "\n", $xml)),
            ENT_QUOTES | ENT_XML1,
            'UTF-8',
        ));
    }

    private function extractXlsxText(string $path): string
    {
        $archive = new ZipArchive;

        if ($archive->open($path) !== true) {
            throw new RuntimeException('Dokumen XLSX tidak dapat dibuka.');
        }

        try {
            $sharedStrings = $this->sharedStrings($archive->getFromName('xl/sharedStrings.xml'));
            $rows = [];

            for ($index = 1; ; $index++) {
                $xml = $archive->getFromName("xl/worksheets/sheet{$index}.xml");

                if (! is_string($xml)) {
                    break;
                }

                $rows = [...$rows, ...$this->worksheetRows($xml, $sharedStrings)];
            }
        } finally {
            $archive->close();
        }

        return $this->normalizeText(implode("\n", $rows));
    }

    /** @return array<int, string> */
    private function sharedStrings(string|false $xml): array
    {
        if (! is_string($xml)) {
            return [];
        }

        $document = simplexml_load_string($xml);

        if ($document === false) {
            throw new RuntimeException('Daftar teks XLSX tidak dapat dibaca.');
        }

        return array_map(
            static fn ($item): string => trim(strip_tags($item->asXML() ?: '')),
            $document->xpath('//*[local-name()="si"]') ?: [],
        );
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, string>
     */
    private function worksheetRows(string $xml, array $sharedStrings): array
    {
        $document = simplexml_load_string($xml);

        if ($document === false) {
            throw new RuntimeException('Lembar kerja XLSX tidak dapat dibaca.');
        }

        $rows = [];

        foreach ($document->xpath('//*[local-name()="row"]') ?: [] as $row) {
            $cells = [];

            foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                $values = $cell->xpath('./*[local-name()="v"]') ?: [];
                $value = trim((string) ($values[0] ?? ''));

                if ((string) $cell['t'] === 's' && isset($sharedStrings[(int) $value])) {
                    $value = $sharedStrings[(int) $value];
                }

                if ($value !== '') {
                    $cells[] = $value;
                }
            }

            if ($cells !== []) {
                $rows[] = implode(' | ', $cells);
            }
        }

        return $rows;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n */', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function registryDocument(KnowledgeBaseDocument $document, string $sourcePath): RegistryDocument
    {
        $documentId = sprintf('KB-UPLOAD-%06d', $document->getKey());

        return new RegistryDocument(
            documentId: $documentId,
            title: trim(str_replace(["\r", "\n"], ' ', $document->name)),
            category: strtolower($document->category),
            documentType: match ($document->category) {
                'Peraturan' => 'official_regulation',
                'SOP' => 'official_procedure',
                'Template' => 'official_template',
                default => 'uploaded_document',
            },
            effectiveDate: $document->effective_date?->format('Y-m-d'),
            priority: match ($document->category) {
                'Peraturan' => 1,
                'SOP' => 2,
                'Template' => 4,
                default => 3,
            },
            status: 'active',
            sourceFile: 'public/'.$document->file_path,
            processedFile: strtolower($documentId).'.md',
            sourceSha256: hash_file('sha256', $sourcePath),
        );
    }

    private function toMarkdown(RegistryDocument $document, string $content): string
    {
        return implode("\n", [
            '---', 'document_id: '.$document->documentId, 'title: '.$document->title,
            'category: '.$document->category, 'document_type: '.$document->documentType,
            'effective_date: '.($document->effectiveDate ?? 'null'), 'priority: '.$document->priority,
            'status: '.$document->status, 'source_file: '.$document->sourceFile,
            'policy_relations: []', '---', '', '# '.$document->title, '', '## Isi Dokumen', '', $content, '',
        ]);
    }

    private function updateRegistry(RegistryDocument $document): void
    {
        $lock = fopen($this->registryPath.'.lock', 'c');

        if ($lock === false || ! flock($lock, LOCK_EX)) {
            throw new RuntimeException('Registry knowledge base tidak dapat dikunci untuk pembaruan.');
        }

        try {
            $registry = is_file($this->registryPath)
                ? json_decode((string) file_get_contents($this->registryPath), true, 512, JSON_THROW_ON_ERROR)
                : ['schema_version' => '1.0', 'documents' => []];

            if (! is_array($registry) || ! isset($registry['documents']) || ! is_array($registry['documents'])) {
                throw new RuntimeException('Registry knowledge base tidak valid.');
            }

            $registry['documents'] = array_values(array_filter(
                $registry['documents'],
                static fn (array $entry): bool => $entry['document_id'] !== $document->documentId,
            ));
            $registry['documents'][] = [
                'document_id' => $document->documentId, 'title' => $document->title,
                'category' => $document->category, 'document_type' => $document->documentType,
                'effective_date' => $document->effectiveDate, 'priority' => $document->priority,
                'status' => $document->status, 'source_file' => $document->sourceFile,
                'processed_file' => $document->processedFile, 'source_sha256' => $document->sourceSha256,
            ];

            $json = json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            $temporaryPath = $this->registryPath.'.'.bin2hex(random_bytes(8)).'.tmp';

            if (file_put_contents($temporaryPath, $json) === false || ! rename($temporaryPath, $this->registryPath)) {
                @unlink($temporaryPath);
                throw new RuntimeException('Registry knowledge base tidak dapat diperbarui.');
            }
        } catch (JsonException $exception) {
            throw new RuntimeException('Registry knowledge base tidak valid.', previous: $exception);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
