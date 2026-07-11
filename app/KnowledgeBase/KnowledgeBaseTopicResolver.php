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
     * substring check, not fuzzy matching.
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
            foreach (self::TOPIC_KEYWORDS[$topic] as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    return $topic;
                }
            }
        }

        return null;
    }

    private function normalize(string $query): string
    {
        $normalized = mb_strtolower(trim($query));
        $normalized = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $normalized) ?? '';
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? '';

        return trim($normalized);
    }
}
