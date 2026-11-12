<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Services\GoogleGuestbookReader;
use App\Support\GuestbookPhone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestbookCheckinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google_guestbook.form_url' => 'https://docs.google.com/forms/d/e/test/viewform']);
    }

    private function pending(string $phone = '6281234567890'): array
    {
        return ['phone_hash' => GuestbookPhone::fingerprint($phone), 'phone_suffix' => substr($phone, -4),
            'expires_at' => now()->addMinutes(29)->timestamp];
    }

    public function test_new_visitors_and_old_acknowledgement_cookies_cannot_access_chatbot(): void
    {
        $this->get(route('chatbot'))->assertOk();
        $this->withCookie('dkp_guestbook_completed', '1')->get(route('chatbot'))->assertOk();
    }

    public function test_checkin_shows_phone_input_and_not_the_old_bypass(): void
    {
        $this->get(route('guestbook.checkin'))->assertOk()->assertSee('Nomor WhatsApp aktif')
            ->assertSee('Isi Buku Tamu')->assertSee('href="https://docs.google.com/forms/d/e/test/viewform"', false)
            ->assertSee('Lanjutkan')->assertDontSee('Saya sudah pernah mengisi');
    }

    public function test_start_stores_normalized_hash_without_full_phone_or_access(): void
    {
        $response = $this->post(route('guestbook.start'), ['phone' => '+62 812-3456-7890']);
        $response->assertRedirect(route('guestbook.checkin'))->assertSessionHas('guestbook_pending.phone_hash', GuestbookPhone::fingerprint('6281234567890'))
            ->assertSessionMissing('guestbook_verified_until');
        $this->assertStringNotContainsString('6281234567890', json_encode(session('guestbook_pending')));
        $this->get(route('guestbook.checkin'))->assertSee('https://docs.google.com/forms/d/e/test/viewform', false)->assertSee('7890');
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $this->post(route('guestbook.start'), ['phone' => '123abc'])->assertSessionHasErrors('phone')->assertSessionMissing('guestbook_pending');
    }

    public function test_missing_form_configuration_does_not_link_to_unrelated_dkp_form(): void
    {
        config(['services.google_guestbook.form_url' => null]);
        $this->get(route('guestbook.checkin'))->assertSee('Tautan Google Form sedang disiapkan')
            ->assertDontSee('Isi Buku Tamu')->assertDontSee('bit.ly/DaftarMagang');
        $this->post(route('guestbook.start'), ['phone' => '081234567890'])->assertSessionHasErrors('verification')->assertSessionMissing('guestbook_pending');
    }

    public function test_completion_without_pending_session_does_not_query_google(): void
    {
        $this->mock(GoogleGuestbookReader::class)->shouldNotReceive('hasResponse');
        $this->post(route('guestbook.complete'))->assertSessionHasErrors('verification')->assertSessionMissing('guestbook_verified_until');
    }

    public function test_expired_pending_session_is_rejected(): void
    {
        $this->mock(GoogleGuestbookReader::class)->shouldNotReceive('hasResponse');
        $pending = $this->pending();
        $pending['expires_at'] = now()->subSecond()->timestamp;
        $this->withSession(['guestbook_pending' => $pending])->post(route('guestbook.complete'))
            ->assertSessionHasErrors('verification')->assertSessionMissing('guestbook_pending')->assertSessionMissing('guestbook_verified_until');
    }

    public function test_logged_in_participants_can_access_chatbot_regardless_of_active_application(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);

        $wopps = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_WOPPS,
            'status' => 'preparation',
        ]);

        $this->actingAs($participant, 'peserta')
            ->withSession(['participant_active_application_id' => $wopps->id])
            ->get(route('chatbot'))
            ->assertOk();

        $magang = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
            'status' => 'preparation',
        ]);

        $this->actingAs($participant, 'peserta')
            ->withSession([
                'participant_active_application_id' => $magang->id,
                'guestbook_verified_until' => now()->addMinutes(5)->timestamp,
            ])
            ->get(route('chatbot'))
            ->assertOk();

        $this->actingAs($participant, 'peserta')
            ->withSession([
                'participant_active_application_id' => null,
                'guestbook_verified_until' => now()->addMinutes(5)->timestamp,
            ])
            ->get(route('chatbot'))
            ->assertOk();
    }

    public function test_unmatched_response_keeps_chatbot_locked(): void
    {
        $this->mock(GoogleGuestbookReader::class)->shouldReceive('hasResponse')->once()->andReturn(false);
        $this->withSession(['guestbook_pending' => $this->pending()])->post(route('guestbook.complete'))
            ->assertSessionHasErrors('verification')->assertSessionMissing('guestbook_verified_until');
        $this->get(route('chatbot'))->assertOk();
    }

    public function test_google_failure_is_safe_and_does_not_expose_error_details(): void
    {
        $this->mock(GoogleGuestbookReader::class)->shouldReceive('hasResponse')->once()->andThrow(new \RuntimeException('private-response-secret'));
        $this->withSession(['guestbook_pending' => $this->pending()])->followingRedirects()->post(route('guestbook.complete'))
            ->assertSee('Pemeriksaan Buku Tamu sedang tidak tersedia')
            ->assertDontSee('private-response-secret')
            ->assertSessionMissing('guestbook_verified_until');
    }

    public function test_all_chatbot_api_routes_reject_unverified_and_expired_sessions(): void
    {
        foreach ([[], ['guestbook_verified_until' => now()->subSecond()->timestamp]] as $session) {
            $this->withSession($session)->getJson(route('chatbot.api.history'))->assertForbidden();
            $this->postJson(route('chatbot.api.messages.send'), ['session_key' => 'test', 'message' => 'Halo'])
                ->assertForbidden()->assertJsonPath('guestbook_url', route('chatbot.service'));
            $this->getJson('/api/chatbot/conversations/999')->assertForbidden();
            $this->postJson('/api/chatbot/messages/999/feedback')->assertForbidden();
            $this->postJson('/api/chatbot/messages/999/escalate')->assertForbidden();
        }
    }

    public function test_repeated_verification_requests_are_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('guestbook.complete'))->assertRedirect();
        }
        $this->post(route('guestbook.complete'))->assertStatus(429);
    }

    public function test_modal_is_only_rendered_for_visitors_without_an_account(): void
    {
        $this->get(route('chatbot'))->assertOk()->assertSee('data-guestbook-dialog', false);
        $this->actingAs(Participant::factory()->create(), 'peserta')->get(route('chatbot'))
            ->assertOk()->assertDontSee('data-guestbook-dialog', false);
    }

    public function test_modal_can_load_and_start_verification_without_redirecting_or_unlocking_chat(): void
    {
        $this->getJson(route('guestbook.checkin'))->assertOk()->assertJsonStructure(['html']);
        $response = $this->postJson(route('guestbook.start'), ['phone' => '081234567890'])
            ->assertOk()->assertJsonPath('allowed', false)->assertSessionMissing('guestbook_verified_until');
        $this->assertStringContainsString('7890', $response->json('html'));
        $this->assertStringNotContainsString('6281234567890', $response->json('html'));
        $this->postJson(route('chatbot.api.messages.send'), ['message' => 'Halo'])->assertForbidden();
        $this->postJson(route('guestbook.start'), ['phone' => '123abc'])->assertUnprocessable()->assertJsonValidationErrors('phone');
    }

    public function test_modal_completion_only_unlocks_after_google_match_and_errors_stay_inline(): void
    {
        $this->mock(GoogleGuestbookReader::class)->shouldReceive('hasResponse')->twice()->andReturn(false, true);
        $this->withSession(['guestbook_pending' => $this->pending()])->postJson(route('guestbook.complete'))
            ->assertUnprocessable()->assertJsonStructure(['message', 'html'])->assertSessionMissing('guestbook_verified_until');
        $this->withSession(['guestbook_pending' => $this->pending()])->postJson(route('guestbook.complete'))
            ->assertOk()->assertJsonPath('allowed', true)->assertSessionHas('guestbook_verified_until')->assertSessionMissing('guestbook_pending');
    }

    public function test_modal_expiration_and_google_failure_keep_access_locked(): void
    {
        $expired = $this->pending();
        $expired['expires_at'] = now()->subSecond()->timestamp;
        $response = $this->withSession(['guestbook_pending' => $expired])->postJson(route('guestbook.complete'))
            ->assertUnprocessable()->assertSessionMissing('guestbook_pending')->assertSessionMissing('guestbook_verified_until');
        $this->assertStringContainsString('guestbook-phone', $response->json('html'));
        $this->mock(GoogleGuestbookReader::class)->shouldReceive('hasResponse')->once()->andThrow(new \RuntimeException('secret'));
        $this->withSession(['guestbook_pending' => $this->pending()])->postJson(route('guestbook.complete'))
            ->assertStatus(503)->assertJsonStructure(['message', 'html'])->assertDontSee('secret')->assertSessionMissing('guestbook_verified_until');
    }

    public function test_reopening_same_service_preserves_pending_verification(): void
    {
        $pending = $this->pending();
        $this->withSession(['chatbot_guest_service' => 'magang_pkl', 'guestbook_pending' => $pending])
            ->postJson(route('chatbot.service.store'), ['service' => 'magang_pkl'])
            ->assertJsonPath('allowed', false)->assertSessionHas('guestbook_pending', $pending);
        $this->postJson(route('chatbot.service.store'), ['service' => 'wopps'])
            ->assertJsonPath('allowed', true)->assertSessionMissing('guestbook_pending');
    }
}
