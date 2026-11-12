<?php

namespace Tests\Feature;

use App\KnowledgeBase\KnowledgeBaseAnswerGenerationResult as Result;
use App\KnowledgeBase\KnowledgeBaseAnswerGenerator;
use App\KnowledgeBase\KnowledgeBaseGroundedContext;
use App\Models\User;
use App\Services\ChatbotAiBudget;
use App\Services\FreeGroqPolicy;
use App\Services\GroundedChatbotResponder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FreeChatbotAiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.groq.enabled' => true, 'services.groq.api_key' => 'institution-test-key',
            'services.groq.model' => FreeGroqPolicy::MODEL, 'services.groq.max_completion_tokens' => 1800,
            'chatbot.ai.account_owner' => 'dkp', 'chatbot.ai.free_plan_confirmed' => true,
            'chatbot.ai.verified_key_hash' => hash('sha256', 'institution-test-key')]);
    }

    private function context(string $query = 'Bagaimana tata krama?', string $content = 'Peserta wajib berpakaian rapi.'): KnowledgeBaseGroundedContext
    {
        return new KnowledgeBaseGroundedContext($query, [['document_id' => 'KB-TEST', 'document_title' => 'Panduan resmi',
            'section_title' => 'Ketentuan', 'content' => $content, 'source_sha256' => hash('sha256', $content)]]);
    }

    private function fakeSuccess(): void
    {
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => 'Peserta wajib berpakaian rapi.'], 'finish_reason' => 'stop']],
            'usage' => ['total_tokens' => 120]], 200, ['x-ratelimit-limit-requests' => '1000',
                'x-ratelimit-limit-tokens' => '8000', 'x-ratelimit-remaining-requests' => '999',
                'x-ratelimit-remaining-tokens' => '7880'])]);
    }

    public function test_personal_unverified_rotated_keys_and_other_models_never_trigger_external_calls(): void
    {
        Http::fake();
        foreach ([['chatbot.ai.account_owner' => 'personal'], ['chatbot.ai.free_plan_confirmed' => false],
            ['services.groq.api_key' => 'different-key'], ['services.groq.model' => 'a-paid-or-unknown-model']] as $override) {
            $old = [];
            foreach ($override as $key => $value) {
                $old[$key] = config($key);
            }
            config($override);
            $this->assertSame(Result::STATUS_PROVIDER_FAILURE, app(KnowledgeBaseAnswerGenerator::class)->generate($this->context())->status);
            config($old);
        }
        Http::assertNothingSent();
        $this->assertDatabaseCount('chatbot_ai_counters', 0);
    }

    public function test_disabled_external_ai_still_answers_from_the_actual_kb_through_responder(): void
    {
        Http::fake();
        config(['services.groq.enabled' => false]);
        $answer = app(GroundedChatbotResponder::class)->answer('Jam kerja peserta magang hari Jumat sampai pukul berapa?');
        $this->assertSame('success', $answer['status']);
        $this->assertStringContainsString('16.30', $answer['answer']);
        $this->assertNotEmpty($answer['sources']);
        Http::assertNothingSent();
    }

    public function test_ai_budget_is_reserved_for_the_whole_site_before_calling_provider(): void
    {
        $this->fakeSuccess();
        config(['chatbot.ai.requests_per_minute' => 1]);
        $generator = app(KnowledgeBaseAnswerGenerator::class);
        $this->assertSame(Result::STATUS_SUCCESS, $generator->generate($this->context())->status);
        $second = $generator->generate($this->context('Pertanyaan lain?'));
        $this->assertSame('local_ai_budget_exhausted', $second->failureCode);
        Http::assertSentCount(1);
        $stats = app(ChatbotAiBudget::class)->snapshot();
        $this->assertSame(1, $stats['requests']);
        $this->assertSame(120, $stats['tokens']);
        $this->assertSame(120, $stats['actual_tokens']);
        $this->assertSame(1, $stats['fallbacks']);
    }

    public function test_token_and_oversize_context_limits_fall_back_without_truncating_or_calling_provider(): void
    {
        Http::fake();
        config(['chatbot.ai.tokens_per_minute' => 10]);
        $this->assertSame('local_ai_budget_exhausted', app(KnowledgeBaseAnswerGenerator::class)->generate($this->context())->failureCode);
        $context = $this->context(content: str_repeat('Isi dokumen resmi yang tetap lengkap. ', 300));
        $before = $context->sources;
        $this->assertSame('context_budget_exceeded', app(KnowledgeBaseAnswerGenerator::class)->generate($context)->failureCode);
        $this->assertSame($before, $context->sources);
        Http::assertNothingSent();
    }

    public function test_reserved_requests_do_not_oversubscribe_and_unknown_usage_keeps_reservation(): void
    {
        config(['chatbot.ai.requests_per_day' => 1]);
        $budget = app(ChatbotAiBudget::class);
        $scope = app(FreeGroqPolicy::class)->budgetScope();
        $reservation = $budget->reserve($scope, 1000);
        $this->assertNotNull($reservation);
        $this->assertNull($budget->reserve($scope, 1000));
        $budget->settle($reservation, false, null, 'provider_connection_failed', []);
        $this->assertSame(1000, $budget->snapshot()['tokens']);
        $this->assertSame(0, $budget->snapshot()['actual_tokens']);
        $this->assertSame(1, $budget->snapshot()['failures']);
    }

    public function test_provider_rate_limit_creates_cooldown_without_retries_or_paid_fallback(): void
    {
        Http::fake(['api.groq.com/*' => Http::response([], 429, ['retry-after' => '60'])]);
        $generator = app(KnowledgeBaseAnswerGenerator::class);
        $this->assertSame('provider_rate_limited', $generator->generate($this->context())->failureCode);
        $this->assertSame('provider_cooldown', $generator->generate($this->context('Pertanyaan lain?'))->failureCode);
        Http::assertSentCount(1);
        $this->travel(61)->seconds();
        $this->assertSame('provider_rate_limited', $generator->generate($this->context('Pertanyaan ketiga?'))->failureCode);
        Http::assertSentCount(2);
    }

    public function test_cached_answer_saves_tokens_but_changed_kb_content_is_never_served_from_old_cache(): void
    {
        $this->fakeSuccess();
        $generator = app(KnowledgeBaseAnswerGenerator::class);
        $generator->generate($this->context());
        $this->assertSame(Result::STATUS_SUCCESS, $generator->generate($this->context())->status);
        Http::assertSentCount(1);
        $this->assertSame(1, app(ChatbotAiBudget::class)->snapshot()['cache_hits']);
        $generator->generate($this->context(content: 'Peraturan telah berubah.'));
        Http::assertSentCount(2);
        $this->travel(16)->minutes();
        $generator->generate($this->context());
        Http::assertSentCount(3);
    }

    public function test_budget_storage_failure_is_fail_closed(): void
    {
        Http::fake();
        DB::partialMock()->shouldReceive('transaction')->andThrow(new \RuntimeException('unavailable'));
        $this->assertSame('provider_controls_unavailable', app(KnowledgeBaseAnswerGenerator::class)->generate($this->context())->failureCode);
        Http::assertNothingSent();
    }

    public function test_provider_truncated_answer_becomes_document_fallback_in_responder(): void
    {
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => 'Kalimat terputus'], 'finish_reason' => 'length']],
            'usage' => ['total_tokens' => 1800]])]);
        $answer = app(GroundedChatbotResponder::class)->answer('Jam kerja peserta magang hari Jumat sampai pukul berapa?');
        $this->assertSame('success', $answer['status']);
        $this->assertStringContainsString('16.30', $answer['answer']);
        $this->assertStringNotContainsString('Kalimat terputus', $answer['answer']);
        Http::assertSentCount(1);
    }

    public function test_admin_monitor_contains_usage_and_warning_but_no_credentials(): void
    {
        $this->fakeSuccess();
        config(['chatbot.ai.requests_per_day' => 1]);
        app(KnowledgeBaseAnswerGenerator::class)->generate($this->context());
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Pemantauan chatbot')->assertSee('Anggaran AI mendekati atau mencapai batas')
            ->assertDontSee('institution-test-key')->assertSee('999');
    }

    public function test_provider_limits_can_tighten_local_budget_and_cached_answers_are_still_available(): void
    {
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => 'Jawaban resmi.']]],
            'usage' => ['total_tokens' => 120]], 200, ['x-ratelimit-limit-requests' => '1'])]);
        $generator = app(KnowledgeBaseAnswerGenerator::class);
        $this->assertSame(Result::STATUS_SUCCESS, $generator->generate($this->context())->status);
        $this->assertSame('local_ai_budget_exhausted', $generator->generate($this->context('Pertanyaan berbeda?'))->failureCode);
        $this->assertSame(Result::STATUS_SUCCESS, $generator->generate($this->context())->status);
        Http::assertSentCount(1);
    }

    public function test_provider_failure_does_not_silence_the_responder(): void
    {
        Http::fake(['api.groq.com/*' => Http::response([], 429, ['retry-after' => '60'])]);
        $result = app(GroundedChatbotResponder::class)->answer('Jam kerja peserta magang hari Jumat sampai pukul berapa?');
        $this->assertSame('success', $result['status']);
        $this->assertStringContainsString('16.30', $result['answer']);
        $this->assertNotEmpty($result['sources']);
        Http::assertSentCount(1);
    }

    public function test_persistent_database_cache_reuses_encrypted_answers_without_storing_plaintext_question(): void
    {
        config(['cache.default' => 'database']);
        $this->fakeSuccess();
        $query = 'Pertanyaan khusus untuk uji cache privat?';
        $generator = app(KnowledgeBaseAnswerGenerator::class);
        $generator->generate($this->context($query));
        $this->assertSame(Result::STATUS_SUCCESS, $generator->generate($this->context($query))->status);
        Http::assertSentCount(1);
        $records = json_encode(DB::table('cache')->get());
        $this->assertStringNotContainsString($query, $records);
        $this->assertStringNotContainsString('Peserta wajib berpakaian rapi.', $records);
        $this->assertStringNotContainsString('institution-test-key', $records);
    }

    public function test_confirmed_key_rotation_cannot_reset_the_websites_daily_budget(): void
    {
        $this->fakeSuccess();
        config(['chatbot.ai.requests_per_day' => 1]);
        app(KnowledgeBaseAnswerGenerator::class)->generate($this->context());
        config(['services.groq.api_key' => 'new-institution-key',
            'chatbot.ai.verified_key_hash' => hash('sha256', 'new-institution-key')]);
        $result = app(KnowledgeBaseAnswerGenerator::class)->generate($this->context());
        $this->assertSame('local_ai_budget_exhausted', $result->failureCode);
        Http::assertSentCount(1);
        $this->assertSame(1, app(ChatbotAiBudget::class)->snapshot()['requests']);
    }
}
