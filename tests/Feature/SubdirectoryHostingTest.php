<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubdirectoryHostingTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://ppid.dkp.jatimprov.go.id/si-melayur';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.url' => self::BASE, 'app.asset_url' => self::BASE]);
        $this->withServerVariables([
            'SCRIPT_NAME' => '/si-melayur/index.php',
            'PHP_SELF' => '/si-melayur/index.php',
            'SCRIPT_FILENAME' => public_path('index.php'),
        ]);
    }

    public function test_chatbot_assets_and_endpoints_stay_inside_the_application_subdirectory(): void
    {
        $this->get(self::BASE.'/chatbot')->assertOk()
            ->assertSee('src="'.self::BASE.'/images/si-melayur-chatbot-ocean.png"', false)
            ->assertSee('data-send-url="'.self::BASE.'/api/chatbot/messages"', false)
            ->assertSee('data-service-choice-url="'.self::BASE.'/chatbot/layanan"', false)
            ->assertSee('data-guestbook-url="'.self::BASE.'/buku-tamu"', false);
    }

    public function test_guestbook_modal_forms_keep_the_subdirectory_prefix(): void
    {
        config(['services.google_guestbook.form_url' => 'https://example.test/guestbook']);
        $response = $this->getJson(self::BASE.'/buku-tamu')->assertOk();
        $this->assertStringContainsString('action="'.self::BASE.'/buku-tamu/mulai"', $response->json('html'));
        $response = $this->postJson(self::BASE.'/buku-tamu/mulai', ['phone' => '081234567890'])
            ->assertOk()->assertJsonPath('allowed', false);
        $this->assertStringContainsString('action="'.self::BASE.'/buku-tamu/selesai"', $response->json('html'));
    }

    public function test_participant_login_redirect_and_service_form_use_the_same_subdirectory(): void
    {
        $this->get(self::BASE.'/peserta/layanan')->assertRedirect(self::BASE.'/akun/masuk');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($participant, 'peserta')->get(self::BASE.'/peserta/layanan')->assertOk()
            ->assertSee('action="'.self::BASE.'/peserta/persiapan-pengajuan"', false)
            ->assertSee('Pilih layanan yang akan dipersiapkan');
    }
}
