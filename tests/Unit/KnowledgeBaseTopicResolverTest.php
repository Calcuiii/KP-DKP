<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\KnowledgeBaseTopicResolver;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class KnowledgeBaseTopicResolverTest extends TestCase
{
    public function test_it_resolves_sertifikat_topic(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        self::assertSame(
            'sertifikat',
            $resolver->resolve('Apakah peserta magang mendapatkan sertifikat?'),
        );
    }

    public function test_it_resolves_surat_keterangan_topic(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        self::assertSame(
            'surat_keterangan',
            $resolver->resolve('Bagaimana cara mendapatkan surat keterangan magang?'),
        );
    }

    public function test_it_resolves_penelitian_permintaan_data_topic(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        self::assertSame(
            'penelitian_permintaan_data',
            $resolver->resolve('Apa syarat pengajuan penelitian dan permintaan data?'),
        );
    }

    public function test_it_resolves_pendaftaran_magang_pkl_topic(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        self::assertSame(
            'pendaftaran_magang_pkl',
            $resolver->resolve('Bagaimana cara pendaftaran magang di sini?'),
        );
    }

    public function test_it_resolves_persyaratan_magang_pkl_topic(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        self::assertSame(
            'persyaratan_magang_pkl',
            $resolver->resolve('Apa saja persyaratan PKL yang harus dipenuhi?'),
        );
    }

    public function test_it_resolves_prosedur_magang_pkl_topic(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        self::assertSame(
            'prosedur_magang_pkl',
            $resolver->resolve('Bagaimana alur magang dari awal sampai selesai?'),
        );
    }

    public function test_it_resolves_contoh_surat_permohonan_topic(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        self::assertSame(
            'contoh_surat_permohonan',
            $resolver->resolve('Ada contoh surat permohonan magang tidak?'),
        );
    }

    public function test_it_resolves_informasi_wajib_surat_permohonan_topic(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        self::assertSame(
            'informasi_wajib_surat_permohonan',
            $resolver->resolve('Informasi apa saja yang wajib ada di surat permohonan?'),
        );
    }

    public function test_it_normalizes_case_and_punctuation_before_matching(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        self::assertSame(
            'sertifikat',
            $resolver->resolve('  SERTIFIKAT??? Bagaimana caranya!!  '),
        );
    }

    public function test_it_applies_deterministic_precedence_for_ambiguous_query(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        // Contains both "surat keterangan" and "sertifikat" keywords.
        // "sertifikat" has higher precedence than "surat_keterangan".
        self::assertSame(
            'sertifikat',
            $resolver->resolve('Bagaimana proses surat keterangan dan sertifikat?'),
        );
    }

    public function test_it_applies_deterministic_precedence_for_pendaftaran_over_prosedur(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        // Contains both "pendaftaran" and "langkah" keywords.
        // "pendaftaran_magang_pkl" has higher precedence than "prosedur_magang_pkl".
        self::assertSame(
            'pendaftaran_magang_pkl',
            $resolver->resolve('Apa langkah-langkah pendaftaran magang?'),
        );
    }

    public function test_it_returns_null_for_unmatched_query(): void
    {
        $resolver = new KnowledgeBaseTopicResolver;

        self::assertNull(
            $resolver->resolve('Cuaca hari ini di Surabaya bagaimana?'),
        );
    }

    public function test_it_rejects_empty_query(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query must not be empty.');

        (new KnowledgeBaseTopicResolver)->resolve('   ');
    }
}
