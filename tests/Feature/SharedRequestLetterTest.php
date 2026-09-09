<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SharedRequestLetterTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_institution_and_filename_do_not_group_different_files(): void
    {
        Storage::fake('local');
        Notification::fake();
        $keys = [];
        foreach (['%PDF-1.4 letter A', '%PDF-1.4 letter B', '%PDF-1.4 letter A'] as $contents) {
            $participant = Participant::factory()->create(['email_verified_at' => now()]);
            $application = $participant->applications()->create([
                'service_type' => 'magang_pkl', 'status' => 'guestbook_submitted', 'guestbook_confirmed_at' => now(),
            ]);
            $this->actingAs($participant, 'peserta')->post(route('peserta.request-letter.store'), [
                'request_letter' => UploadedFile::fake()->createWithContent('surat.pdf', $contents),
                'letter_declaration' => '1', 'shared_letter' => '1', 'letter_institution' => 'Kampus Contoh',
            ])->assertRedirect()->assertSessionHasNoErrors();
            $keys[] = $application->documents()->sole()->letter_group_key;
        }
        $this->assertNotSame($keys[0], $keys[1]);
        $this->assertSame($keys[0], $keys[2]);
        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))
            ->assertOk()->assertDontSee('name="letter_number"', false);
    }

    public function test_five_participants_can_upload_the_same_letter_with_independent_reviews(): void
    {
        Storage::fake('local');
        Notification::fake();
        $documents = collect();
        foreach (['Andi', 'Budi', 'Citra', 'Dini', 'Eko'] as $name) {
            $participant = Participant::factory()->create(['name' => $name, 'email_verified_at' => now()]);
            $application = $participant->applications()->create([
                'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
                'status' => 'guestbook_submitted', 'guestbook_confirmed_at' => now(),
            ]);
            $this->actingAs($participant, 'peserta')->post(route('peserta.request-letter.store'), [
                'request_letter' => UploadedFile::fake()->create('surat-bersama.pdf', 10, 'application/pdf'),
                'letter_declaration' => '1', 'shared_letter' => '1',
                'letter_institution' => 'Universitas Contoh',
            ])->assertRedirect()->assertSessionHasNoErrors();
            $documents->push($application->documents()->sole());
        }
        $this->assertCount(1, $documents->pluck('letter_group_key')->unique());
        $this->assertCount(5, $documents->pluck('file_path')->unique());
        $admin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $this->actingAs($admin, 'web')->get(route('admin.pemeriksaan-dokumen.show', $documents[0]))
            ->assertOk()->assertSee('Kelompok surat bersama')->assertSee('5 pengajuan');
        $this->actingAs($admin, 'web')->patch(route('admin.pemeriksaan-dokumen.approve', $documents[0]))
            ->assertSessionHasErrors('participant_identity_confirmed');
        $this->actingAs($admin, 'web')->patch(route('admin.pemeriksaan-dokumen.approve', $documents[0]), [
            'participant_identity_confirmed' => '1',
        ])->assertSessionHasNoErrors();
        $this->actingAs($admin, 'web')->patch(route('admin.pemeriksaan-dokumen.revision', $documents[1]), [
            'review_notes' => 'Nama belum tercantum pada lampiran.',
        ])->assertSessionHasNoErrors();
        $this->assertSame('approved', $documents[0]->fresh()->review_status);
        $this->assertSame('revision_required', $documents[1]->fresh()->review_status);
        $this->assertSame('submitted', $documents[2]->fresh()->review_status);
        $this->assertNotSame(
            ParticipantApplicationDocument::letterGroupKey('Universitas Lain', hash('sha256', '')),
            $documents[0]->letter_group_key
        );
    }

    public function test_repeated_draft_submission_does_not_create_another_application(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        for ($i = 0; $i < 2; $i++) {
            $this->actingAs($participant, 'peserta')->post(route('peserta.application.store'), [
                'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
            ])->assertRedirect();
        }
        $this->assertSame(1, $participant->applications()->count());
    }

    public function test_shared_letter_requires_metadata_and_cannot_be_reused_by_the_same_participant(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $previous = $participant->applications()->create(['service_type' => 'magang_pkl', 'status' => 'rejected']);
        $previous->forceFill(['created_at' => now()->subDay()])->save();
        $previous->documents()->create([
            'type' => 'request_letter', 'version' => 1, 'file_path' => 'old.pdf',
            'original_name' => 'old.pdf', 'mime_type' => 'application/pdf', 'file_size' => 10,
            'review_status' => 'submitted',
        ])->forceFill(['letter_group_key' => ParticipantApplicationDocument::letterGroupKey('Kampus', hash('sha256', ''))])->save();
        $current = $participant->applications()->create([
            'service_type' => 'magang_pkl', 'status' => 'guestbook_submitted', 'guestbook_confirmed_at' => now(),
        ]);
        $this->actingAs($participant, 'peserta')->post(route('peserta.request-letter.store'), [
            'request_letter' => UploadedFile::fake()->create('same.pdf', 10, 'application/pdf'),
            'letter_declaration' => '1', 'shared_letter' => '1',
        ])->assertSessionHasErrors(['letter_institution']);
        $this->actingAs($participant, 'peserta')->post(route('peserta.request-letter.store'), [
            'request_letter' => UploadedFile::fake()->create('same.pdf', 10, 'application/pdf'),
            'letter_declaration' => '1', 'shared_letter' => '1',
            'letter_institution' => ' KAMPUS ',
        ])->assertSessionHasErrors('request_letter');
        $this->assertSame(0, $current->documents()->count());
    }
}
