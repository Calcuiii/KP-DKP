<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\KnowledgeBase\GroqKnowledgeBaseAnswerGenerator;
use App\KnowledgeBase\KnowledgeBaseAnswerGenerationResult;
use App\KnowledgeBase\KnowledgeBaseGroundedContext;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GroqKnowledgeBaseAnswerGeneratorTest extends TestCase
{
    public function test_it_generates_a_grounded_answer_through_groq(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => 'Peserta dapat menghubungi WhatsApp layanan untuk koordinasi kuota.',
                    ],
                ]],
            ]),
        ]);

        $result = $this->generator()->generate($this->context());

        self::assertSame(KnowledgeBaseAnswerGenerationResult::STATUS_SUCCESS, $result->status);
        self::assertSame(
            'Peserta dapat menghubungi WhatsApp layanan untuk koordinasi kuota.',
            $result->answer,
        );
        self::assertSame([[
            'document_id' => 'KB-TEST',
            'document_title' => 'Dokumen Pengujian',
            'section_title' => 'Kontak Koordinasi',
        ]], $result->sources);

        Http::assertSent(static function (Request $request): bool {
            $messages = $request->data()['messages'];

            return $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
                && $request->hasHeader('Authorization', 'Bearer test-groq-key')
                && $request->data()['model'] === 'llama-3.1-8b-instant'
                && str_contains($messages[0]['content'], 'Gunakan hanya fakta')
                && $request->data()['temperature'] === 0
                && str_contains($messages[1]['content'], 'CHECKLIST CAKUPAN')
                && str_contains($messages[0]['content'], 'Jangan menyatakan bahwa suatu langkah')
                && str_contains($messages[1]['content'], '0852 53000 485');
        });
    }

    public function test_it_returns_insufficient_information_when_the_model_declines_the_context(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => 'INSUFFICIENT_INFORMATION'],
                ]],
            ]),
        ]);

        $result = $this->generator()->generate($this->context());

        self::assertSame(
            KnowledgeBaseAnswerGenerationResult::STATUS_INSUFFICIENT_INFORMATION,
            $result->status,
        );
        self::assertSame([], $result->sources);
    }

    public function test_it_treats_an_insufficient_information_prefix_as_an_internal_signal(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "INSUFFICIENT_INFORMATION\nTidak ada data yang dapat saya pastikan.",
                    ],
                ]],
            ]),
        ]);

        $result = $this->generator()->generate($this->context());

        self::assertSame(
            KnowledgeBaseAnswerGenerationResult::STATUS_INSUFFICIENT_INFORMATION,
            $result->status,
        );
        self::assertSame([], $result->sources);
    }

    public function test_it_returns_provider_failure_for_unsuccessful_provider_responses(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([], 500),
        ]);

        $result = $this->generator()->generate($this->context());

        self::assertSame(
            KnowledgeBaseAnswerGenerationResult::STATUS_PROVIDER_FAILURE,
            $result->status,
        );
        self::assertSame('provider_request_failed', $result->failureCode);
    }

    public function test_it_does_not_call_groq_when_the_provider_is_not_configured(): void
    {
        Http::fake();

        $generator = new GroqKnowledgeBaseAnswerGenerator(
            enabled: false,
            apiKey: null,
            model: 'llama-3.1-8b-instant',
            timeoutSeconds: 15,
            maxCompletionTokens: 700,
        );
        $result = $generator->generate($this->context());

        self::assertSame(
            KnowledgeBaseAnswerGenerationResult::STATUS_PROVIDER_FAILURE,
            $result->status,
        );
        self::assertSame('provider_not_configured', $result->failureCode);
        Http::assertNothingSent();
    }

    private function generator(): GroqKnowledgeBaseAnswerGenerator
    {
        return new GroqKnowledgeBaseAnswerGenerator(
            enabled: true,
            apiKey: 'test-groq-key',
            model: 'llama-3.1-8b-instant',
            timeoutSeconds: 15,
            maxCompletionTokens: 700,
        );
    }

    private function context(): KnowledgeBaseGroundedContext
    {
        return new KnowledgeBaseGroundedContext(
            'Bagaimana cara koordinasi kuota?',
            [[
                'chunk_id' => 'KB-TEST::section-001',
                'document_id' => 'KB-TEST',
                'document_title' => 'Dokumen Pengujian',
                'section_title' => 'Kontak Koordinasi',
                'section_index' => 1,
                'content' => 'Koordinasi kuota dilakukan melalui WhatsApp 0852 53000 485.',
                'score' => 10,
                'category' => 'pengujian',
                'document_type' => 'official_document',
                'priority' => 1,
                'status' => 'active',
                'source_file' => 'source.pdf',
                'source_sha256' => 'test-sha256',
            ]],
        );
    }
}
