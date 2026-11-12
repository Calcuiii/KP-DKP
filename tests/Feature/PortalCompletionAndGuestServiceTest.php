<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalCompletionAndGuestServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_composer_is_locked_until_inline_service_selection(): void
    {
        $this->get(route('chatbot'))->assertOk()->assertSee('data-chat-allowed="false"', false)
            ->assertSee('data-chat-topic="wopps"', false);
        $this->postJson(route('chatbot.api.messages.send'), ['message' => 'Halo'])->assertForbidden();
        $this->postJson(route('chatbot.service.store'), ['service' => 'wopps'])->assertOk()->assertJsonPath('allowed', true);
        $this->get(route('chatbot'))->assertSee('data-chat-allowed="true"', false);
        $this->postJson(route('chatbot.service.store'), ['service' => 'none'])->assertJsonPath('allowed', false);
        $this->postJson(route('chatbot.api.messages.send'), ['message' => 'Halo'])->assertForbidden();
        $this->postJson(route('chatbot.service.store'), ['service' => 'magang_pkl'])
            ->assertJsonPath('allowed', false)->assertJsonPath('redirect', route('guestbook.checkin'));
    }

    public function test_logged_in_participant_can_ask_without_guest_service_or_guestbook(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($participant, 'peserta')->get(route('chatbot'))->assertOk()
            ->assertSee('data-chat-allowed="true"', false)->assertSee('data-chat-guest="false"', false);
        $this->getJson(route('chatbot.api.history', ['session_key' => (string) Str::uuid()]))->assertOk();
        $participant->applications()->create(['service_type' => 'magang_pkl', 'status' => 'preparation']);
        $this->getJson(route('chatbot.api.history', ['session_key' => (string) Str::uuid()]))->assertOk();
    }

    public function test_guest_selects_service_and_wopps_needs_no_guestbook(): void
    {
        $this->get(route('chatbot'))->assertOk();
        $this->get(route('chatbot.service'))->assertRedirect(route('chatbot'));
        $this->post(route('chatbot.service.store'), ['service' => 'other'])->assertSessionHasErrors('service');
        $this->post(route('chatbot.service.store'), ['service' => 'wopps'])->assertRedirect(route('chatbot'));
        $this->get(route('chatbot'))->assertOk();
        $this->getJson(route('chatbot.api.history', ['session_key' => (string) Str::uuid()]))->assertOk();
        $this->post(route('chatbot.service.store'), ['service' => 'magang_pkl'])->assertRedirect(route('guestbook.checkin'));
        $this->get(route('chatbot'))->assertOk();
        $this->getJson(route('chatbot.api.history'))->assertForbidden();
        $this->withSession(['guestbook_verified_until' => now()->addHour()->timestamp])->get(route('chatbot'))->assertOk();
        $this->withSession(['guestbook_verified_until' => now()->subSecond()->timestamp])->get(route('chatbot'))->assertOk();
    }

    public function test_completion_requires_owner_final_date_and_both_proofs_and_is_idempotent(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => 'magang_pkl', 'decision' => 'accepted', 'status' => 'accepted',
            'official_ended_at' => now()->addDay(),
        ]);
        $url = route('peserta.internship.complete', $application);
        $this->post($url)->assertRedirect();
        $other = Participant::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($other, 'peserta')->post($url)->assertForbidden();
        $this->actingAs($participant, 'peserta')->post($url)->assertStatus(422);
        $application->update(['official_ended_at' => now()->subDay(), 'presentation_submitted_at' => now(), 'presentation_date' => now()->subDay()]);
        $this->post($url)->assertStatus(422);
        $application->update(['completion_form_submitted_at' => now(), 'service_type' => 'wopps']);
        $this->post($url)->assertStatus(422);
        $application->update(['service_type' => 'magang_pkl']);
        $this->post($url)->assertRedirect(route('peserta.dashboard'))->assertSessionHas('internship_celebration', true);
        $finished = $application->fresh()->completed_at;
        $this->assertNotNull($finished);
        $this->get(route('peserta.dashboard'))->assertOk()->assertSee('Selamat, magang Anda telah selesai!')
            ->assertSee(route('peserta.services').'#persiapan', false);
        $this->travel(1)->minutes();
        $this->post($url)->assertSessionHas('internship_celebration', false);
        $this->assertTrue($finished->equalTo($application->fresh()->completed_at));
    }

    public function test_infographics_offer_download_in_both_categories(): void
    {
        $this->get(route('infographics'))->assertOk()->assertSee('Unduh infografis')->assertSee('data-infographic-download', false);
        $this->get(route('infographics', ['layanan' => 'wopps']))->assertOk()->assertSee('Unduh infografis');
    }

    public function test_finished_participant_can_return_to_services_and_reopen_the_original_application(): void
    {
        $this->get(route('peserta.services'))->assertRedirect(route('peserta.login'));
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $finished = $participant->applications()->create([
            'service_type' => 'magang_pkl', 'decision' => 'accepted', 'status' => 'completed',
            'official_ended_at' => now()->subDay(), 'completed_at' => now(),
            'presentation_date' => now()->subDay(),
            'presentation_submitted_at' => now(), 'completion_form_submitted_at' => now(),
            'certificate_path' => 'certificates/existing.pdf', 'certificate_published_at' => now(),
        ]);
        $original = $finished->fresh()->getAttributes();
        $other = Participant::factory()->create(['email_verified_at' => now()])->applications()
            ->create(['service_type' => 'wopps', 'status' => 'preparation']);
        $this->actingAs($participant, 'peserta')->withSession(['participant_active_application_id' => $finished->id])
            ->get(route('peserta.services'))->assertOk()->assertViewHas('application', null)
            ->assertSee('Pilih layanan yang akan dipersiapkan')->assertSee('Riwayat pengajuan')
            ->assertSee(route('peserta.application.select', $finished), false)
            ->assertDontSee(route('peserta.application.select', $other), false);
        $this->get(route('peserta.services'))->assertOk()->assertViewHas('application', null);
        $this->assertSame($original, $finished->fresh()->getAttributes());
        $this->assertSame(1, $participant->applications()->count());
        $this->get(route('peserta.application.select', $other))->assertForbidden();
        $this->get(route('peserta.application.select', $finished))->assertRedirect(route('peserta.dashboard'));
        $this->get(route('peserta.dashboard'))->assertOk()->assertSee('Selamat, magang Anda telah selesai!')
            ->assertSee(route('peserta.certificate.download', $finished), false);
        $this->get(route('peserta.services'))->assertOk();
        $this->post(route('peserta.application.store'), ['service_type' => 'wopps'])
            ->assertRedirect(route('peserta.dashboard'));
        $this->assertSame(2, $participant->applications()->count());
        $this->assertSame($original, $finished->fresh()->getAttributes());
        $this->get(route('peserta.dashboard'))->assertOk()->assertViewHas('application', fn ($app) => $app->service_type === 'wopps');
    }

    public function test_new_internship_after_completion_does_not_reuse_the_completed_application(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $finished = $participant->applications()->create([
            'service_type' => 'magang_pkl', 'status' => 'completed', 'completed_at' => now(),
        ]);
        $this->actingAs($participant, 'peserta')->get(route('peserta.services'))->assertOk();
        $this->post(route('peserta.application.store'), ['service_type' => 'magang_pkl'])
            ->assertRedirect(route('peserta.dashboard'));
        $newApplication = $participant->applications()->latest('id')->first();
        $this->assertNotSame($finished->id, $newApplication->id);
        $this->assertSame('preparation', $newApplication->status);
        $this->assertNull($newApplication->completed_at);
        $this->assertSame('completed', $finished->fresh()->status);
        $this->assertNotNull($finished->fresh()->completed_at);
        $this->get(route('peserta.dashboard'))->assertOk()->assertViewHas('application', fn ($app) => $app->id === $newApplication->id);
    }
}
