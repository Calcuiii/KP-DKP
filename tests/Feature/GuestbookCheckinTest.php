<?php

namespace Tests\Feature;

use App\Services\GoogleGuestbookReader;
use App\Support\GuestbookPhone;
use Tests\TestCase;

class GuestbookCheckinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google_guestbook.form_url' => 'https://docs.google.com/forms/d/e/test/viewform']);
    }

    private function pending(string $phone = '6281234567890'): array
    {
        return ['phone_hash' => GuestbookPhone::fingerprint($phone), 'phone_suffix' => substr($phone, -4),
            'requested_at' => now()->subMinute()->timestamp, 'expires_at' => now()->addMinutes(29)->timestamp];
    }

    public function test_new_visitors_and_old_acknowledgement_cookies_cannot_access_chatbot(): void
    {
        $this->get(route('chatbot'))->assertRedirect(route('guestbook.checkin'));
        $this->withCookie('dkp_guestbook_completed', '1')->get(route('chatbot'))->assertRedirect(route('guestbook.checkin'));
    }

    public function test_checkin_shows_phone_input_and_not_the_old_bypass(): void
    {
        $this->get(route('guestbook.checkin'))->assertOk()->assertSee('Nomor WhatsApp aktif')
            ->assertSee('Mulai Verifikasi')->assertDontSee('Saya sudah pernah mengisi');
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
        $this->get(route('guestbook.checkin'))->assertSee('Tautan Google Form sedang disiapkan')->assertDontSee('bit.ly/DaftarMagang');
        $this->post(route('guestbook.start'), ['phone' => '081234567890'])->assertSessionHasErrors('verification')->assertSessionMissing('guestbook_pending');
    }

    public function test_completion_without_pending_session_does_not_query_google(): void
    {
        $this->mock(GoogleGuestbookReader::class)->shouldNotReceive('hasRecentResponse');
        $this->post(route('guestbook.complete'))->assertSessionHasErrors('verification')->assertSessionMissing('guestbook_verified_until');
    }

    public function test_expired_pending_session_is_rejected(): void
    {
        $this->mock(GoogleGuestbookReader::class)->shouldNotReceive('hasRecentResponse');
        $pending = $this->pending();
        $pending['expires_at'] = now()->subSecond()->timestamp;
        $this->withSession(['guestbook_pending' => $pending])->post(route('guestbook.complete'))
            ->assertSessionHasErrors('verification')->assertSessionMissing('guestbook_pending')->assertSessionMissing('guestbook_verified_until');
    }

    public function test_only_matched_phone_grants_access_using_the_server_session(): void
    {
        $pending = $this->pending();
        $this->mock(GoogleGuestbookReader::class)->shouldReceive('hasRecentResponse')->once()
            ->with($pending['phone_hash'], $pending['requested_at'])->andReturn(true);
        $this->withSession(['guestbook_pending' => $pending])->post(route('guestbook.complete'), ['phone' => '081111111111'])
            ->assertRedirect(route('chatbot'))->assertSessionHas('guestbook_verified_until')->assertSessionMissing('guestbook_pending');
        $this->get(route('chatbot'))->assertOk();
    }

    public function test_unmatched_response_keeps_chatbot_locked(): void
    {
        $this->mock(GoogleGuestbookReader::class)->shouldReceive('hasRecentResponse')->once()->andReturn(false);
        $this->withSession(['guestbook_pending' => $this->pending()])->post(route('guestbook.complete'))
            ->assertSessionHasErrors('verification')->assertSessionMissing('guestbook_verified_until');
        $this->get(route('chatbot'))->assertRedirect(route('guestbook.checkin'));
    }

    public function test_google_failure_is_safe_and_does_not_expose_error_details(): void
    {
        $this->mock(GoogleGuestbookReader::class)->shouldReceive('hasRecentResponse')->once()->andThrow(new \RuntimeException('private-response-secret'));
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
                ->assertForbidden()->assertJsonPath('guestbook_url', route('guestbook.checkin'));
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
}
