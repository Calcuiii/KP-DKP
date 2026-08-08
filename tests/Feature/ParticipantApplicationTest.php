<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_a_participant_can_upload_guestbook_proof_for_a_magang_application(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create(['service_type' => ParticipantApplication::SERVICE_MAGANG_PKL, 'status' => 'preparation']);

        $this->actingAs($participant, 'peserta')->post(route('peserta.guestbook-proof.store'), [
            'guestbook_proof' => UploadedFile::fake()->image('bukti-buku-tamu.png'),
            'guestbook_declaration' => '1',
        ])->assertRedirect()->assertSessionHas('status');

        $application->refresh();
        $proof = $application->documents()->sole();
        $this->assertSame(ParticipantApplicationDocument::TYPE_GUESTBOOK, $proof->type);
        $this->assertSame('guestbook_submitted', $application->status);
        $this->assertNotNull($application->guestbook_confirmed_at);
        Storage::disk('local')->assertExists($proof->file_path);
    }

    public function test_the_internship_guestbook_button_opens_the_form_directly_without_the_chatbot_gate(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
            'status' => 'preparation',
        ]);

        $this->actingAs($participant, 'peserta')
            ->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee(config('services.dkp.internship_guestbook_url'), false)
            ->assertSee('Buka Form Buku Tamu')
            ->assertDontSee('href="'.route('guestbook.checkin').'"', false);
    }

    public function test_request_letter_requires_guestbook_proof_and_is_versioned(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create(['service_type' => ParticipantApplication::SERVICE_MAGANG_PKL, 'status' => 'preparation']);

        $this->actingAs($participant, 'peserta')->post(route('peserta.request-letter.store'), [
            'request_letter' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
            'letter_declaration' => '1',
        ])->assertStatus(422);

        $application->update(['guestbook_confirmed_at' => now()]);

        $this->actingAs($participant, 'peserta')->post(route('peserta.request-letter.store'), [
            'request_letter' => UploadedFile::fake()->create('surat-v1.pdf', 100, 'application/pdf'),
            'letter_declaration' => '1',
        ])->assertRedirect();

        $application->documents()->where('type', ParticipantApplicationDocument::TYPE_REQUEST_LETTER)->sole()
            ->update(['review_status' => ParticipantApplicationDocument::REVIEW_REVISION, 'review_notes' => 'Perbaiki tanggal.']);

        $this->actingAs($participant, 'peserta')->post(route('peserta.request-letter.store'), [
            'request_letter' => UploadedFile::fake()->create('surat-v2.pdf', 100, 'application/pdf'),
            'letter_declaration' => '1',
        ])->assertRedirect();

        $this->assertSame([1, 2], $application->documents()->where('type', ParticipantApplicationDocument::TYPE_REQUEST_LETTER)->orderBy('version')->pluck('version')->all());
        $this->assertSame('letter_revision_required', $application->fresh()->status);
        $this->assertSame('unreadable', $application->documents()->latest('version')->first()->automated_check_status);
    }

    public function test_google_form_confirmation_requires_an_approved_letter(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create(['service_type' => ParticipantApplication::SERVICE_MAGANG_PKL, 'status' => 'letter_under_review', 'guestbook_confirmed_at' => now()]);
        $letter = $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_REQUEST_LETTER, 'version' => 1,
            'file_path' => 'test/surat.pdf', 'original_name' => 'surat.pdf', 'mime_type' => 'application/pdf',
            'file_size' => 100, 'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $this->actingAs($participant, 'peserta')->post(route('peserta.google-form.confirm'))->assertStatus(422);
        $letter->update(['review_status' => ParticipantApplicationDocument::REVIEW_APPROVED]);
        $this->actingAs($participant, 'peserta')->post(route('peserta.google-form.confirm'))->assertRedirect();

        $this->assertSame('response_pending', $application->fresh()->status);
        $this->assertNotNull($application->fresh()->google_form_confirmed_at);
    }

    public function test_a_participant_cannot_download_another_participants_document(): void
    {
        Storage::fake('local');
        $owner = Participant::factory()->create(['email_verified_at' => now()]);
        $intruder = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $owner->applications()->create(['service_type' => ParticipantApplication::SERVICE_MAGANG_PKL, 'status' => 'preparation']);
        Storage::disk('local')->put('private/surat.pdf', 'pdf');
        $document = $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_REQUEST_LETTER, 'version' => 1,
            'file_path' => 'private/surat.pdf', 'original_name' => 'surat.pdf', 'mime_type' => 'application/pdf',
            'file_size' => 3, 'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $this->actingAs($intruder, 'peserta')->get(route('peserta.document.download', $document))->assertForbidden();
        $this->actingAs($owner, 'peserta')->get(route('peserta.document.download', $document))->assertOk();
    }
}
