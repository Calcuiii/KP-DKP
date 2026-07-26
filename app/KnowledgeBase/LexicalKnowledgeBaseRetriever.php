<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

use InvalidArgumentException;
use Normalizer;
use RuntimeException;

final class LexicalKnowledgeBaseRetriever
{
    /**
     * Query tokens filtered out of token-level scoring because they are
     * Indonesian interrogative/functional words with no evidenced
     * independent contribution to relevant matches. Negation, existence,
     * and intent verbs are intentionally excluded to preserve meaning.
     *
     * @var array<int, string>
     */
    private const STOPWORD_QUERY_TOKENS = [
        'apa',
        'apakah',
        'bagaimana',
        'dapatkah',
        'siapa',
        'kapan',
        'dimana',
        'mengapa',
        'kenapa',
        'adakah',
        'tolong',
        'mohon',
        'ya',
        'dong',
        'kah',
        'yang',
    ];

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
        $informativeQueryTokens = $this->filterInformativeTokens($queryTokens);
        $results = [];
        foreach ($chunks as $index => $chunk) {
            if (! $chunk instanceof KnowledgeBaseChunk) {
                throw new RuntimeException(
                    sprintf('Knowledge base chunk at index %d has invalid type.', $index),
                );
            }
            $scoring = $this->score(
                $normalizedQuery,
                $informativeQueryTokens,
                $chunk,
            );

            if ($scoring['score'] > 0 && $scoring['has_direct_match']) {
                $results[] = new KnowledgeBaseSearchResult(
                    chunk: $chunk,
                    score: $scoring['score'],
                    directMatchTokenCount: $scoring['direct_match_token_count'],
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
     * @return array{score: int, has_direct_match: bool, direct_match_token_count: int}
     */
    private function score(
        string $normalizedQuery,
        array $queryTokens,
        KnowledgeBaseChunk $chunk,
    ): array {
        $sectionTitle = $this->normalize($chunk->sectionTitle);
        $documentTitle = $this->normalize($chunk->documentTitle);
        $content = $this->normalize($chunk->content);
        $score = 0;
        $hasDirectMatch = false;
        $directMatchTokens = [];

        if (str_contains($sectionTitle, $normalizedQuery)) {
            $score += 12;
            $hasDirectMatch = true;
        }
        if (str_contains($documentTitle, $normalizedQuery)) {
            $score += 2;
        }
        if (str_contains($content, $normalizedQuery)) {
            $score += 6;
            $hasDirectMatch = true;
        }

        $sectionTokens = array_fill_keys($this->tokenize($sectionTitle), true);
        $documentTokens = array_fill_keys($this->tokenize($documentTitle), true);
        $contentTokens = array_fill_keys($this->tokenize($content), true);

        foreach ($queryTokens as $token) {
            if (isset($sectionTokens[$token])) {
                $score += 6;
                $hasDirectMatch = true;
                $directMatchTokens[$token] = true;
            }
            if (isset($documentTokens[$token])) {
                $score += 1;
            }
            if (isset($contentTokens[$token])) {
                $score += 2;
                $hasDirectMatch = true;
                $directMatchTokens[$token] = true;
            }
        }

        return [
            'score' => $score,
            'has_direct_match' => $hasDirectMatch,
            'direct_match_token_count' => count($directMatchTokens),
        ];
    }

    /**
     * @param  array<int, string>  $tokens
     * @return array<int, string>
     */
    private function filterInformativeTokens(array $tokens): array
    {
        return array_values(array_diff($tokens, self::STOPWORD_QUERY_TOKENS));
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
