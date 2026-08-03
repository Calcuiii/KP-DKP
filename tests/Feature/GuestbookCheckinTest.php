<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuestbookCheckinTest extends TestCase
{
    public function test_the_chatbot_redirects_new_visitors_to_the_guestbook_checkin(): void
    {
        $this->get(route('chatbot'))
            ->assertRedirect(route('guestbook.checkin'));
    }

    public function test_the_guestbook_checkin_page_links_to_the_official_google_form(): void
    {
        $this->get(route('guestbook.checkin'))
            ->assertOk()
            ->assertSee('Sebelum menggunakan layanan, silakan isi Buku Tamu')
            ->assertSee('https://bit.ly/DaftarMagangPKL_DKP_JATIM', false)
            ->assertSee('Saya sudah pernah mengisi');
    }

    public function test_an_acknowledged_visitor_is_returned_to_the_chatbot_and_receives_a_cookie(): void
    {
        $response = $this->from(route('chatbot'))
            ->post(route('guestbook.complete'));

        $response
            ->assertRedirect(route('chatbot'))
            ->assertCookie('dkp_guestbook_completed', '1');
    }

    public function test_an_acknowledged_visitor_can_access_the_chatbot(): void
    {
        $this->withCookie('dkp_guestbook_completed', '1')
            ->get(route('chatbot'))
            ->assertOk();
    }

    public function test_the_chatbot_api_rejects_visitors_without_guestbook_checkin(): void
    {
        $this->postJson(route('chatbot.api.messages.send'), [
            'session_key' => 'guestbook-checkin-test',
            'message' => 'Halo',
        ])
            ->assertForbidden()
            ->assertJsonPath('guestbook_url', route('guestbook.checkin'));
    }
}
