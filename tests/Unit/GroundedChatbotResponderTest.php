<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GroundedChatbotResponder;
use Tests\TestCase;

final class GroundedChatbotResponderTest extends TestCase
{
    public function test_it_returns_a_compact_grounded_answer_without_markdown_headings(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'Apa saja persyaratan pengajuan magang?',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_SUCCESS, $result['status']);
        self::assertStringStartsWith(
            "Berikut informasi yang tersedia pada dokumen resmi:\n",
            $result['answer'],
        );
        self::assertStringNotContainsString('## ', $result['answer']);
        self::assertStringNotContainsString("\n\n", $result['answer']);
        self::assertStringContainsString(
            'Anda dapat membuka bagian sumber di bawah untuk melihat dokumen lengkap.',
            $result['answer'],
        );
    }

    public function test_it_keeps_the_insufficient_information_response_when_no_source_matches(): void
    {
        $result = app(GroundedChatbotResponder::class)->answer(
            'kataunikyangtidakada123456789',
        );

        self::assertSame(GroundedChatbotResponder::STATUS_INSUFFICIENT_INFORMATION, $result['status']);
        self::assertSame([], $result['sources']);
    }
}
