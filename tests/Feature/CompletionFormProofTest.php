<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompletionFormProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_completes_stage_seven_and_is_private(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create(['service_type' => 'magang_pkl', 'decision' => 'accepted', 'status' => 'accepted', 'presentation_date' => now()->toDateString(), 'presentation_submitted_at' => now()]);
        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))->assertSee('Tahap 8 terkunci');
        $this->post(route('peserta.completion-proof.store', $application), ['completion_form_proof' => UploadedFile::fake()->image('confirmation.png')])->assertSessionHasNoErrors()->assertRedirect(route('peserta.dashboard').'#form-selesai');
        $application->refresh();
        Storage::disk('local')->assertExists($application->completion_form_proof_path);
        $this->assertNotNull($application->completion_form_submitted_at);
        $this->get(route('peserta.dashboard'))->assertSee('Bukti pengisian form tersimpan')->assertDontSee('Tahap 8 terkunci');
        $this->get(route('peserta.completion-proof.download', $application))->assertDownload();
        $other = Participant::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($other, 'peserta')->get(route('peserta.completion-proof.download', $application))->assertForbidden();
        $this->post(route('peserta.completion-proof.store', $application))->assertForbidden();
        $this->actingAs($participant, 'peserta')->post(route('peserta.completion-proof.store', $application), ['completion_form_proof' => UploadedFile::fake()->image('replacement.png')])->assertSessionHasNoErrors();
        $this->assertNotSame($application->completion_form_proof_path, $application->fresh()->completion_form_proof_path);
    }

    public function test_stage_six_and_valid_image_are_required(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create(['service_type' => 'magang_pkl', 'decision' => 'accepted', 'status' => 'accepted']);
        $this->actingAs($participant, 'peserta')->post(route('peserta.completion-proof.store', $application))->assertStatus(422);
        $application->update(['presentation_submitted_at' => now()]);
        $this->post(route('peserta.completion-proof.store', $application))->assertSessionHasErrors('completion_form_proof');
        $this->post(route('peserta.completion-proof.store', $application), ['completion_form_proof' => UploadedFile::fake()->create('proof.pdf', 10, 'application/pdf')])->assertSessionHasErrors('completion_form_proof');
        $this->post(route('peserta.completion-proof.store', $application), ['completion_form_proof' => UploadedFile::fake()->image('large.jpg')->size(5121)])->assertSessionHasErrors('completion_form_proof');
        $this->assertNull($application->fresh()->completion_form_submitted_at);
        $this->post(route('peserta.completion-proof.store', $application), ['completion_form_proof' => UploadedFile::fake()->image('proof.webp')])->assertSessionHasErrors('completion_form_proof');
        $application->update(['decision' => 'rejected']);
        $this->post(route('peserta.completion-proof.store', $application))->assertForbidden();
        $application->update(['decision' => 'accepted', 'service_type' => 'wopps']);
        $this->post(route('peserta.completion-proof.store', $application))->assertForbidden();
        $this->assertEmpty(Storage::disk('local')->allFiles());
    }

    public function test_existing_certificate_cannot_bypass_incomplete_previous_stages(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        Storage::disk('local')->put('certificates/test.pdf', 'test');
        $application = $participant->applications()->create([
            'service_type' => 'magang_pkl', 'decision' => 'accepted', 'status' => 'accepted',
            'presentation_date' => now(), 'presentation_submitted_at' => now(),
            'certificate_path' => 'certificates/test.pdf', 'certificate_published_at' => now(),
        ]);
        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))->assertOk()
            ->assertSee('Tahap 8 terkunci')->assertDontSee('Unduh sertifikat PDF');
        $this->get(route('peserta.certificate.download', $application))->assertForbidden();
        $application->update(['presentation_submitted_at' => null]);
        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))->assertOk()
            ->assertSee('Tahap 7 terkunci')->assertDontSee('name="completion_form_proof"', false)
            ->assertDontSee('https://bit.ly/SelesaiMagangPKL-SM');
    }
}
