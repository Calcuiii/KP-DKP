<?php

declare(strict_types=1);

namespace App\Services;

final class GroundedChatbotResponseSectionOrderer
{
    /**
     * Keeps document groups in their first selected order while presenting
     * sections from the same document in their original document order.
     *
     * @param  array<int, array{
     *     source: array{document_id: string, section_index: int},
     *     content: string
     * }>  $selectedSections
     * @return array<int, array{
     *     source: array{document_id: string, section_index: int},
     *     content: string
     * }>
     */
    public function order(array $selectedSections): array
    {
        $documentOrder = [];
        $sectionsByDocument = [];

        foreach ($selectedSections as $position => $selectedSection) {
            $documentId = $selectedSection['source']['document_id'];

            if (! array_key_exists($documentId, $sectionsByDocument)) {
                $documentOrder[] = $documentId;
                $sectionsByDocument[$documentId] = [];
            }

            $sectionsByDocument[$documentId][] = [
                'position' => $position,
                'selected_section' => $selectedSection,
            ];
        }

        $orderedSections = [];

        foreach ($documentOrder as $documentId) {
            usort(
                $sectionsByDocument[$documentId],
                static function (array $left, array $right): int {
                    $sectionIndexComparison = $left['selected_section']['source']['section_index']
                        <=> $right['selected_section']['source']['section_index'];

                    return $sectionIndexComparison !== 0
                        ? $sectionIndexComparison
                        : $left['position'] <=> $right['position'];
                },
            );

            foreach ($sectionsByDocument[$documentId] as $section) {
                $orderedSections[] = $section['selected_section'];
            }
        }

        return $orderedSections;
    }
}
