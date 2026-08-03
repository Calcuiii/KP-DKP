<?php

declare(strict_types=1);

namespace App\Services;

final class GroundedChatbotContextSourcePrioritizer
{
    private const MAXIMUM_ADDITIONAL_DOCUMENTS = 2;

    /**
     * Prioritizes every eligible section from the document with the
     * highest-ranked section, then retains ranked sections from no more than
     * two other documents. The caller remains responsible for applying any
     * character budget to the returned order.
     *
     * @param  array<int, array{document_id: string, score: int}>  $sources
     * @return array<int, array{document_id: string, score: int}>
     */
    public function prioritize(array $sources): array
    {
        if ($sources === []) {
            return [];
        }

        $dominantDocumentId = $sources[0]['document_id'];
        $dominantSources = [];
        $additionalSources = [];
        $additionalDocumentIds = [];

        foreach ($sources as $source) {
            if ($source['document_id'] === $dominantDocumentId) {
                $dominantSources[] = $source;

                continue;
            }

            $documentId = $source['document_id'];

            if (! array_key_exists($documentId, $additionalDocumentIds)) {
                if (count($additionalDocumentIds) >= self::MAXIMUM_ADDITIONAL_DOCUMENTS) {
                    continue;
                }

                $additionalDocumentIds[$documentId] = true;
            }

            $additionalSources[] = $source;
        }

        return [...$dominantSources, ...$additionalSources];
    }
}
