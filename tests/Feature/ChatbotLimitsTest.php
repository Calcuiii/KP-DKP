<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatbotLimitsTest extends TestCase
{
    use RefreshDatabase;

    private function ask()
    {
        return $this->withCredentials()->withCookie(config('session.cookie'), session()->getId())->postJson(route('chatbot.api.messages.send'), ['session_key' => (string) Str::uuid(),
            'message' => 'Bagaimana alur pengajuan Magang / PKL?']);
    }

    public function test_guest_message_limit_is_enforced_without_counting_history_or_feedback(): void
    {
        Http::fake();
        $this->withSession(['chatbot_guest_service' => 'wopps']);
        for ($i = 0; $i < 5; $i++) {
            $this->ask()->assertCreated();
        }
        $this->ask()->assertStatus(429)->assertJsonPath('code', 'CHATBOT_MESSAGE_LIMIT')->assertHeader('Retry-After');
        $this->getJson(route('chatbot.api.history', ['session_key' => (string) Str::uuid()]))->assertOk();
        $this->postJson(route('chatbot.api.messages.feedback', 999), ['session_key' => (string) Str::uuid(), 'rating' => 'positive'])->assertNotFound();
        $this->travel(61)->seconds();
        $this->ask()->assertCreated();
        Http::assertNothingSent();
    }

    public function test_daily_limit_survives_minute_reset_and_uuid_changes(): void
    {
        config(['chatbot.guest_per_day' => 2]);
        $this->withSession(['chatbot_guest_service' => 'wopps']);
        $this->ask()->assertCreated();
        $this->ask()->assertCreated();
        $this->travel(61)->seconds();
        $this->ask()->assertStatus(429)->assertJsonPath('code', 'CHATBOT_MESSAGE_LIMIT');
        $this->travel(24)->hours();
        $this->ask()->assertCreated();
    }

    public function test_account_limits_are_separate_on_shared_ip_and_follow_accounts_across_ips(): void
    {
        config(['chatbot.account_per_minute' => 1]);
        $one = Participant::factory()->create(['email_verified_at' => now()]);
        $two = Participant::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($one, 'peserta');
        $this->ask()->assertCreated();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20']);
        $this->ask()->assertStatus(429);
        $this->actingAs($two, 'peserta');
        $this->ask()->assertCreated();
    }

    public function test_ip_backstop_prevents_new_guest_sessions_bypassing_limits(): void
    {
        config(['chatbot.ip_per_day' => 1]);
        $this->withSession(['chatbot_guest_service' => 'wopps']);
        $this->ask()->assertCreated();
        session()->invalidate();
        $this->withSession(['chatbot_guest_service' => 'wopps']);
        $this->ask()->assertStatus(429);
    }
}
