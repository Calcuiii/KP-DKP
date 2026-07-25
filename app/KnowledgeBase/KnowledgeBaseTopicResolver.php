<?php

declare(strict_types=1);

namespace App\KnowledgeBase;

use InvalidArgumentException;

final class KnowledgeBaseTopicResolver
{
    /**
     * Deterministic precedence order, most specific/unambiguous topic first.
     * When a normalized query matches keyword rules for multiple topics,
     * the topic that appears earliest in this list wins.
     *
     * @var array<int, string>
     */
    private const TOPIC_PRECEDENCE = [
        'sertifikat',
        'surat_keterangan',
        'penelitian_permintaan_data',
        'pendaftaran_magang_pkl',
        'persyaratan_magang_pkl',
        'prosedur_magang_pkl',
        'contoh_surat_permohonan',
        'informasi_wajib_surat_permohonan',
    ];

    /**
     * Keyword/phrase rules per canonical topic. Matching is a normalized
     * substring check, except prosedur_magang_pkl which uses token-based
     * matching to recognize separated alur and Magang/PKL terms.
     *
     * @var array<string, array<int, string>>
     */
    private const TOPIC_KEYWORDS = [
        'sertifikat' => [
            'sertifikat',
        ],
        'surat_keterangan' => [
            'surat keterangan',
        ],
        'penelitian_permintaan_data' => [
            'penelitian',
            'permintaan data',
            'wawancara',
            'observasi',
            'ethical clearance',
            'proposal kegiatan',
            'sampling',
        ],
        'pendaftaran_magang_pkl' => [
            'pendaftaran',
            'daftar magang',
            'daftar pkl',
        ],
        'persyaratan_magang_pkl' => [
            'persyaratan',
            'syarat magang',
            'syarat pkl',
        ],
        'prosedur_magang_pkl' => [
            'prosedur',
            'alur magang',
            'alur pkl',
            'langkah',
            'tata cara',
        ],
        'contoh_surat_permohonan' => [
            'contoh surat',
            'template surat',
            'contoh isian',
        ],
        'informasi_wajib_surat_permohonan' => [
            'informasi wajib',
            'informasi yang wajib',
            'yang wajib',
            'isi surat permohonan',
        ],
    ];

    /**
     * Resolve a natural-language query into one canonical supported topic.
     *
     * Returns null when the normalized query does not match any known
     * topic rule. This resolver never invents a fallback topic.
     */
    public function resolve(string $query): ?string
    {
        if (trim($query) === '') {
            throw new InvalidArgumentException('Query must not be empty.');
        }

        $normalized = $this->normalize($query);

        foreach (self::TOPIC_PRECEDENCE as $topic) {
            if ($this->matchesTopic($topic, $normalized)) {
                return $topic;
            }
        }

        return null;
    }

    private function matchesTopic(string $topic, string $normalizedQuery): bool
    {
        if ($topic === 'prosedur_magang_pkl') {
            return $this->matchesProcedureMagangPkl($normalizedQuery);
        }

        foreach (self::TOPIC_KEYWORDS[$topic] as $keyword) {
            if (str_contains($normalizedQuery, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function matchesProcedureMagangPkl(string $normalizedQuery): bool
    {
        $tokens = array_fill_keys(explode(' ', $normalizedQuery), true);

        if (isset($tokens['prosedur'])) {
            return true;
        }

        if (isset($tokens['tata']) && isset($tokens['cara'])) {
            return true;
        }

        if ($this->matchesExplicitLowerPriorityTopic($normalizedQuery)) {
            return false;
        }

        if (isset($tokens['langkah'])) {
            return isset($tokens['magang'])
                || isset($tokens['pkl'])
                || isset($tokens['alur']);
        }

        return isset($tokens['alur'])
            && (isset($tokens['magang']) || isset($tokens['pkl']));
    }

    private function matchesExplicitLowerPriorityTopic(string $normalizedQuery): bool
    {
        foreach ([
            'contoh_surat_permohonan',
            'informasi_wajib_surat_permohonan',
        ] as $topic) {
            foreach (self::TOPIC_KEYWORDS[$topic] as $keyword) {
                if (str_contains($normalizedQuery, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function normalize(string $query): string
    {
        $normalized = mb_strtolower(trim($query));
        $normalized = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $normalized) ?? '';
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? '';

        return trim($normalized);
    }
}
