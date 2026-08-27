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

    public function test_an_accepted_internship_uses_the_execution_dashboard_instead_of_the_old_stage_cards(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
            'status' => 'accepted',
            'decision' => 'accepted',
            'official_started_at' => now()->subDays(2),
            'official_ended_at' => now()->addDays(12),
        ]);

        $this->actingAs($participant, 'peserta')
            ->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee('Selamat menjalankan kegiatan')
            ->assertSee('Kalender kegiatan magang')
            ->assertSee('Persiapan laporan &amp; presentasi', false)
            ->assertSee('Periode persiapan laporan dan presentasi')
            ->assertSee('H-10 sampai H-1')
            ->assertSee('data-preparation-window-day', false)
            ->assertSee('Lihat bulan sebelumnya')
            ->assertSee('Lihat bulan berikutnya')
            ->assertSee('data-calendar-month-panel', false)
            ->assertSee('Tahap persiapan telah selesai')
            ->assertDontSee('Keputusan dan surat balasan Dinas');
    }

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
            ->assertSee('Pemeriksaan Surat Permohonan')
            ->assertSee('Nomor Induk Mahasiswa')
            ->assertSee('Dosen Pembimbing/Lapangan dan WhatsApp aktif')
            ->assertSee('Tujuan Penggunaan Data / Informasi');
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
        $latestLetter = $application->documents()->latest('version')->first();
        $this->assertSame('unreadable', $latestLetter->automated_check_status);
        $this->assertSame(ParticipantApplicationDocument::REVIEW_SUBMITTED, $latestLetter->review_status);
        $this->assertNotNull($latestLetter->automated_check_results);
        $this->assertNotNull($latestLetter->automated_checked_at);
        $this->assertNull($latestLetter->reviewed_at);
    }

    public function test_wopps_request_letter_can_be_uploaded_without_guestbook_proof(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_WOPPS,
            'status' => 'preparation',
        ]);

        $this->actingAs($participant, 'peserta')->post(route('peserta.request-letter.store'), [
            'request_letter' => UploadedFile::fake()->create('surat-wopps.pdf', 100, 'application/pdf'),
            'letter_declaration' => '1',
        ])->assertRedirect()->assertSessionHas('status');

        $letter = $application->documents()->sole();
        $this->assertSame(ParticipantApplicationDocument::TYPE_REQUEST_LETTER, $letter->type);
        $this->assertSame('unreadable', $letter->automated_check_status);
        $this->assertSame('letter_revision_required', $application->fresh()->status);
    }

    public function test_wopps_ethics_approval_is_locked_until_request_letter_is_approved(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_WOPPS,
            'status' => 'preparation',
        ]);

        $payload = [
            'ethics_approval' => UploadedFile::fake()->create('ethics.pdf', 100, 'application/pdf'),
            'ethics_declaration' => '1',
        ];
        $this->actingAs($participant, 'peserta')->post(route('peserta.ethics-approval.store'), $payload)->assertStatus(422);

        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
            'version' => 1,
            'file_path' => 'test/request.pdf',
            'original_name' => 'request.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'review_status' => ParticipantApplicationDocument::REVIEW_APPROVED,
        ]);

        $this->actingAs($participant, 'peserta')->post(route('peserta.ethics-approval.store'), $payload)->assertRedirect()->assertSessionHas('status');
        $ethics = $application->documents()->where('type', ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL)->sole();
        $this->assertSame('unreadable', $ethics->automated_check_status);
        $this->assertSame('ethics_revision_required', $application->fresh()->status);
    }

    public function test_wopps_form_proof_is_locked_until_ethics_approval_is_approved(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_WOPPS,
            'status' => 'ethics_under_review',
        ]);

        $payload = [
            'wopps_form_proof' => UploadedFile::fake()->image('bukti-wopps.png'),
            'wopps_form_declaration' => '1',
        ];

        $this->actingAs($participant, 'peserta')
            ->post(route('peserta.wopps-form-proof.store'), $payload)
            ->assertStatus(422);

        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL,
            'version' => 1,
            'file_path' => 'test/ethics.pdf',
            'original_name' => 'ethics.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'review_status' => ParticipantApplicationDocument::REVIEW_APPROVED,
        ]);

        $this->actingAs($participant, 'peserta')
            ->post(route('peserta.wopps-form-proof.store'), $payload)
            ->assertRedirect()
            ->assertSessionHas('status');

        $proof = $application->documents()->where('type', ParticipantApplicationDocument::TYPE_WOPPS_FORM_PROOF)->sole();
        $this->assertSame('bukti-wopps.png', $proof->original_name);
        $this->assertSame(ParticipantApplicationDocument::REVIEW_SUBMITTED, $proof->review_status);
        $this->assertSame('wopps_form_submitted', $application->fresh()->status);
        $this->assertNotNull($application->fresh()->google_form_confirmed_at);
        Storage::disk('local')->assertExists($proof->file_path);
    }

    public function test_approved_ethics_document_unlocks_the_wopps_form_stage_on_dashboard(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_WOPPS,
            'status' => 'ethics_approved',
        ]);
        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL,
            'version' => 1,
            'file_path' => 'test/ethics.pdf',
            'original_name' => 'ethics.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'review_status' => ParticipantApplicationDocument::REVIEW_APPROVED,
        ]);

        $this->actingAs($participant, 'peserta')
            ->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['WOPPS · Tahap 2', 'WOPPS · Tahap 3'])
            ->assertSee('https://bit.ly/WOPPS', false)
            ->assertSee('Upload bukti pengiriman');
    }

    public function test_submitted_wopps_form_proof_unlocks_the_official_contact_stage(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_WOPPS,
            'status' => 'wopps_form_submitted',
            'google_form_confirmed_at' => now(),
        ]);
        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_WOPPS_FORM_PROOF,
            'version' => 1,
            'file_path' => 'test/bukti-wopps.png',
            'original_name' => 'bukti-wopps.png',
            'mime_type' => 'image/png',
            'file_size' => 100,
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $this->actingAs($participant, 'peserta')
            ->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee('Koordinasi tindak lanjut')
            ->assertSee('Bapak Dicky Fadillah')
            ->assertSee('+62 852-5300-0485')
            ->assertSee('https://wa.me/6285253000485', false);
    }

    public function test_internship_form_proof_requires_an_approved_letter_and_unlocks_the_response_stage(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create(['service_type' => ParticipantApplication::SERVICE_MAGANG_PKL, 'status' => 'letter_under_review', 'guestbook_confirmed_at' => now()]);
        $letter = $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_REQUEST_LETTER, 'version' => 1,
            'file_path' => 'test/surat.pdf', 'original_name' => 'surat.pdf', 'mime_type' => 'application/pdf',
            'file_size' => 100, 'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $payload = [
            'internship_form_proof' => UploadedFile::fake()->image('bukti-form.png'),
            'internship_form_declaration' => '1',
        ];

        $this->actingAs($participant, 'peserta')->post(route('peserta.internship-form-proof.store'), $payload)->assertStatus(422);
        $letter->update(['review_status' => ParticipantApplicationDocument::REVIEW_APPROVED]);
        $this->actingAs($participant, 'peserta')->post(route('peserta.internship-form-proof.store'), [
            'internship_form_proof' => UploadedFile::fake()->image('bukti-form.png'),
            'internship_form_declaration' => '1',
        ])->assertRedirect();

        $this->assertSame('response_pending', $application->fresh()->status);
        $this->assertNotNull($application->fresh()->google_form_confirmed_at);
        $this->assertDatabaseHas('participant_application_documents', [
            'participant_application_id' => $application->id,
            'type' => ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF,
            'original_name' => 'bukti-form.png',
        ]);
    }

    public function test_approved_internship_application_shows_google_forms_for_both_education_levels(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
            'status' => 'letter_under_review',
            'guestbook_confirmed_at' => now(),
            'letter_submitted_at' => now(),
        ]);

        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
            'version' => 1,
            'file_path' => 'participant-applications/test/request-letter.pdf',
            'original_name' => 'surat.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'review_status' => ParticipantApplicationDocument::REVIEW_APPROVED,
        ]);

        $this->actingAs($participant, 'peserta')
            ->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee('https://tinyurl.com/DaftarMagangDKP-SM', false)
            ->assertSee('https://tinyurl.com/DaftarMagangDKP-PT', false)
            ->assertSee('SMA/SMK')
            ->assertSee('Perguruan Tinggi');
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
