<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GroundedChatbotResponseSectionOrderer;
use PHPUnit\Framework\TestCase;

final class GroundedChatbotResponseSectionOrdererTest extends TestCase
{
    public function test_it_orders_sections_from_the_same_document_by_section_index(): void
    {
        $orderedSections = $this->orderer()->order([
            $this->selectedSection('DOC-A', 5, 'content-five'),
            $this->selectedSection('DOC-A', 1, 'content-one'),
            $this->selectedSection('DOC-A', 10, 'content-ten'),
        ]);

        self::assertSame([1, 5, 10], $this->sectionIndexes($orderedSections));
    }

    public function test_it_keeps_document_groups_in_their_first_selected_order(): void
    {
        $orderedSections = $this->orderer()->order([
            $this->selectedSection('DOC-B', 3, 'content-b-three'),
            $this->selectedSection('DOC-A', 5, 'content-a-five'),
            $this->selectedSection('DOC-C', 2, 'content-c-two'),
            $this->selectedSection('DOC-A', 1, 'content-a-one'),
            $this->selectedSection('DOC-B', 1, 'content-b-one'),
            $this->selectedSection('DOC-A', 10, 'content-a-ten'),
        ]);

        self::assertSame(
            ['DOC-B', 'DOC-B', 'DOC-A', 'DOC-A', 'DOC-A', 'DOC-C'],
            $this->documentIds($orderedSections),
        );
        self::assertSame([1, 3, 1, 5, 10, 2], $this->sectionIndexes($orderedSections));
    }

    public function test_it_keeps_the_original_selected_order_when_section_indexes_tie(): void
    {
        $orderedSections = $this->orderer()->order([
            $this->selectedSection('DOC-A', 1, 'first-content'),
            $this->selectedSection('DOC-A', 1, 'second-content'),
        ]);

        self::assertSame(['first-content', 'second-content'], $this->contents($orderedSections));
    }

    public function test_it_keeps_source_metadata_paired_with_its_content_after_ordering(): void
    {
        $orderedSections = $this->orderer()->order([
            $this->selectedSection('DOC-A', 2, 'content-for-two'),
            $this->selectedSection('DOC-A', 1, 'content-for-one'),
        ]);

        self::assertSame('source-title-1', $orderedSections[0]['source']['section_title']);
        self::assertSame('content-for-one', $orderedSections[0]['content']);
        self::assertSame('source-title-2', $orderedSections[1]['source']['section_title']);
        self::assertSame('content-for-two', $orderedSections[1]['content']);
    }

    private function orderer(): GroundedChatbotResponseSectionOrderer
    {
        return new GroundedChatbotResponseSectionOrderer;
    }

    /**
     * @return array{
     *     source: array{document_id: string, section_index: int, section_title: string},
     *     content: string
     * }
     */
    private function selectedSection(
        string $documentId,
        int $sectionIndex,
        string $content,
    ): array {
        return [
            'source' => [
                'document_id' => $documentId,
                'section_index' => $sectionIndex,
                'section_title' => 'source-title-'.strtolower((string) $sectionIndex),
            ],
            'content' => $content,
        ];
    }

    /**
     * @param  array<int, array{source: array{section_index: int}, content: string}>  $sections
     * @return array<int, int>
     */
    private function sectionIndexes(array $sections): array
    {
        return array_map(
            static fn (array $section): int => $section['source']['section_index'],
            $sections,
        );
    }

    /**
     * @param  array<int, array{source: array{document_id: string}, content: string}>  $sections
     * @return array<int, string>
     */
    private function documentIds(array $sections): array
    {
        return array_map(
            static fn (array $section): string => $section['source']['document_id'],
            $sections,
        );
    }

    /**
     * @param  array<int, array{source: array, content: string}>  $sections
     * @return array<int, string>
     */
    private function contents(array $sections): array
    {
        return array_map(
            static fn (array $section): string => $section['content'],
            $sections,
        );
    }
}
