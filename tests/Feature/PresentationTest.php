<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_evidence_unlocks_form_and_remains_private(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create(['service_type' => 'magang_pkl', 'status' => 'accepted', 'decision' => 'accepted']);
        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))->assertSee('Tahap 7 terkunci')->assertDontSee('https://bit.ly/SelesaiMagangPKL-SM');
        $this->post(route('peserta.presentation.store', $application), ['presentation_date' => now()->toDateString(), 'presentation_photo' => UploadedFile::fake()->image('photo.jpg')])->assertSessionHasNoErrors()->assertRedirect();
        $application->refresh();
        Storage::disk('local')->assertExists($application->presentation_photo_path);
        $this->assertNotNull($application->presentation_submitted_at);
        $this->get(route('peserta.dashboard'))->assertSee('Bukti presentasi tersimpan')->assertSee('https://bit.ly/SelesaiMagangPKL-SM');
        $this->get(route('peserta.presentation.download', $application))->assertOk();
        $other = Participant::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($other, 'peserta')->get(route('peserta.presentation.download', $application))->assertForbidden();
        $this->post(route('peserta.presentation.store', $application), [])->assertForbidden();
    }

    public function test_future_dates_and_non_photos_do_not_unlock_form(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create(['service_type' => 'magang_pkl', 'status' => 'accepted', 'decision' => 'accepted']);
        $this->actingAs($participant, 'peserta')->post(route('peserta.presentation.store', $application), ['presentation_date' => now()->addDay()->toDateString(), 'presentation_photo' => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf')])->assertSessionHasErrors(['presentation_date', 'presentation_photo']);
        $this->assertNull($application->fresh()->presentation_submitted_at);
        $this->post(route('peserta.presentation.store', $application), ['presentation_date' => now()->toDateString(), 'presentation_photo' => UploadedFile::fake()->image('photo.webp')])->assertSessionHasErrors('presentation_photo');
    }

    public function test_existing_account_can_supply_institution_and_phone(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($participant, 'peserta')->patch(route('peserta.profile.details'), ['institution' => 'SMK Contoh', 'phone' => '+628123456789'])->assertSessionHasNoErrors();
        $this->assertSame('SMK Contoh', $participant->fresh()->institution);
        $this->assertSame('+628123456789', $participant->fresh()->phone);
    }
}
