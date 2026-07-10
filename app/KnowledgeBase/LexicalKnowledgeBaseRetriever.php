<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

use InvalidArgumentException;
use Normalizer;
use RuntimeException;

final class LexicalKnowledgeBaseRetriever
{
    /**
     * @param  array<int, KnowledgeBaseChunk>  $chunks
     * @return array<int, KnowledgeBaseSearchResult>
     */
    public function retrieve(string $query, array $chunks, int $topK = 5): array
    {
        if ($topK < 1) {
            throw new InvalidArgumentException('topK must be greater than zero.');
        }

        $normalizedQuery = $this->normalize($query);

        if ($normalizedQuery === '') {
            throw new InvalidArgumentException('Query must not be empty.');
        }

        $queryTokens = $this->tokenize($normalizedQuery);
        $results = [];

        foreach ($chunks as $index => $chunk) {
            if (! $chunk instanceof KnowledgeBaseChunk) {
                throw new RuntimeException(
                    sprintf('Knowledge base chunk at index %d has invalid type.', $index),
                );
            }

            $score = $this->score($normalizedQuery, $queryTokens, $chunk);

            if ($score > 0) {
                $results[] = new KnowledgeBaseSearchResult(
                    chunk: $chunk,
                    score: $score,
                );
            }
        }

        usort(
            $results,
            static function (
                KnowledgeBaseSearchResult $left,
                KnowledgeBaseSearchResult $right,
            ): int {
                return $right->score <=> $left->score
                    ?: $left->chunk->priority <=> $right->chunk->priority
                    ?: strcmp($left->chunk->documentId, $right->chunk->documentId)
                    ?: $left->chunk->sectionIndex <=> $right->chunk->sectionIndex
                    ?: strcmp($left->chunk->chunkId, $right->chunk->chunkId);
            },
        );

        return array_slice($results, 0, $topK);
    }

    /**
     * @param  array<int, string>  $queryTokens
     */
    private function score(
        string $normalizedQuery,
        array $queryTokens,
        KnowledgeBaseChunk $chunk,
    ): int {
        $sectionTitle = $this->normalize($chunk->sectionTitle);
        $documentTitle = $this->normalize($chunk->documentTitle);
        $content = $this->normalize($chunk->content);

        $score = 0;

        if (str_contains($sectionTitle, $normalizedQuery)) {
            $score += 12;
        }

        if (str_contains($documentTitle, $normalizedQuery)) {
            $score += 8;
        }

        if (str_contains($content, $normalizedQuery)) {
            $score += 6;
        }

        $sectionTokens = array_fill_keys($this->tokenize($sectionTitle), true);
        $documentTokens = array_fill_keys($this->tokenize($documentTitle), true);
        $contentTokens = array_fill_keys($this->tokenize($content), true);

        foreach ($queryTokens as $token) {
            if (isset($sectionTokens[$token])) {
                $score += 5;
            }

            if (isset($documentTokens[$token])) {
                $score += 3;
            }

            if (isset($contentTokens[$token])) {
                $score += 1;
            }
        }

        return $score;
    }

    private function normalize(string $text): string
    {
        $text = Normalizer::normalize($text, Normalizer::FORM_KC);

        if ($text === false) {
            throw new RuntimeException('Unable to normalize retrieval text.');
        }

        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text);

        if ($text === null) {
            throw new RuntimeException('Unable to normalize retrieval text.');
        }

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $normalizedText): array
    {
        if ($normalizedText === '') {
            return [];
        }

        return array_values(array_unique(explode(' ', $normalizedText)));
    }
}
