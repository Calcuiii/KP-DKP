<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use App\Services\GoogleGuestbookReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ParticipantChatbotAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_login_opens_chatbot_and_api_without_guestbook_and_logout_revokes_access(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now(), 'password' => 'password123']);
        $this->mock(GoogleGuestbookReader::class)->shouldNotReceive('hasResponse');
        $this->post(route('peserta.login.store'), ['email' => $participant->email, 'password' => 'password123'])->assertSessionHasNoErrors();
        $this->get(route('chatbot'))->assertOk()->assertSessionMissing('guestbook_verified_until');
        $key = (string) Str::uuid();
        $this->getJson(route('chatbot.api.history', ['session_key' => $key]))->assertOk()->assertJsonPath('data', []);
        // Passing the access gate reaches input validation without calling an AI provider.
        $this->postJson(route('chatbot.api.messages.send'), ['session_key' => $key, 'message' => ''])->assertUnprocessable()->assertJsonValidationErrors('message');
        $this->post(route('peserta.logout'))->assertRedirect();
        $this->get(route('chatbot'))->assertOk();
        $this->getJson(route('chatbot.api.history', ['session_key' => $key]))->assertForbidden();
    }

    public function test_logged_in_accounts_can_use_chatbot_without_the_guest_service_gate(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => null]);
        $this->actingAs($participant, 'peserta')->get(route('chatbot'))->assertOk();
        $this->getJson(route('chatbot.api.history', ['session_key' => (string) Str::uuid()]))->assertOk();
        auth('peserta')->logout();
        $this->actingAs(User::factory()->create(), 'web')->get(route('chatbot'))->assertOk();
    }
}
