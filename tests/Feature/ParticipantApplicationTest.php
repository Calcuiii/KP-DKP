<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_verified_participant_can_create_a_magang_pkl_preparation_draft(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($participant, 'peserta')
            ->post(route('peserta.application.store'), ['service_type' => ParticipantApplication::SERVICE_MAGANG_PKL])
            ->assertRedirect(route('peserta.dashboard'))
            ->assertSessionHas('status', 'Persiapan pengajuan Anda telah disimpan.');

        $this->assertDatabaseHas('participant_applications', [
            'participant_id' => $participant->id,
            'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
            'status' => 'preparation',
        ]);
    }

    public function test_a_participant_can_change_the_service_type_while_still_in_preparation(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
            'status' => 'preparation',
        ]);

        $this->actingAs($participant, 'peserta')
            ->post(route('peserta.application.store'), ['service_type' => ParticipantApplication::SERVICE_WOPPS])
            ->assertRedirect(route('peserta.dashboard'));

        $this->assertSame(1, $participant->applications()->count());
        $this->assertSame(ParticipantApplication::SERVICE_WOPPS, $participant->applications()->sole()->service_type);
    }

    public function test_the_dashboard_shows_service_specific_preparation_information(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_WOPPS,
            'status' => 'preparation',
        ]);

        $this->actingAs($participant, 'peserta')
            ->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee('WOPPS')
            ->assertSee('Persetujuan etik')
            ->assertSee('AI Document Checker')
            ->assertSee('Terbuka setelah pemeriksaan dokumen tersedia.');
    }

    public function test_an_unverified_participant_cannot_create_a_preparation_draft(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => null]);

        $this->actingAs($participant, 'peserta')
            ->post(route('peserta.application.store'), ['service_type' => ParticipantApplication::SERVICE_MAGANG_PKL])
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('participant_applications', 0);
    }
}
