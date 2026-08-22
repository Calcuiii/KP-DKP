<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDocumentReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_review_page_submits_both_decisions_using_patch(): void
    {
        [$admin, $document] = $this->reviewFixture();

        $this->actingAs($admin)
            ->get(route('admin.pemeriksaan-dokumen.show', $document))
            ->assertOk()
            ->assertSeeInOrder([
                'name="_method" value="PATCH"',
                'Setujui Surat',
                'name="_method" value="PATCH"',
                'Minta Perbaikan',
            ], false);
    }

    public function test_an_admin_can_approve_a_submitted_request_letter(): void
    {
        [$admin, $document, $application] = $this->reviewFixture();

        $this->actingAs($admin)
            ->patch(route('admin.pemeriksaan-dokumen.approve', $document), [
                'review_notes' => 'Surat sudah sesuai.',
            ])
            ->assertRedirect(route('admin.pemeriksaan-dokumen.show', $document));

        $document->refresh();
        $this->assertSame(ParticipantApplicationDocument::REVIEW_APPROVED, $document->review_status);
        $this->assertSame('Surat sudah sesuai.', $document->review_notes);
        $this->assertNotNull($document->reviewed_at);
        $this->assertSame('letter_approved', $application->fresh()->status);

        $notification = $application->participant->notifications()->sole();
        $this->assertSame('approved', $notification->data['status']);
        $this->assertSame('Dokumen disetujui', $notification->data['title']);
        $this->assertSame('Surat sudah sesuai.', $notification->data['review_notes']);
    }

    public function test_an_admin_can_request_a_revision_with_required_notes(): void
    {
        [$admin, $document, $application] = $this->reviewFixture();

        $this->actingAs($admin)
            ->patch(route('admin.pemeriksaan-dokumen.revision', $document), [
                'review_notes' => 'Tanggal selesai belum tercantum.',
            ])
            ->assertRedirect(route('admin.pemeriksaan-dokumen.show', $document));

        $document->refresh();
        $this->assertSame(ParticipantApplicationDocument::REVIEW_REVISION, $document->review_status);
        $this->assertSame('Tanggal selesai belum tercantum.', $document->review_notes);
        $this->assertNotNull($document->reviewed_at);
        $this->assertSame('letter_revision_required', $application->fresh()->status);

        $notification = $application->participant->notifications()->sole();
        $this->assertSame('revision_required', $notification->data['status']);
        $this->assertSame('Perbaikan dokumen diperlukan', $notification->data['title']);
        $this->assertSame('Tanggal selesai belum tercantum.', $notification->data['review_notes']);
    }

    public function test_a_participant_can_read_their_notification(): void
    {
        [$admin, $document, $application] = $this->reviewFixture();

        $this->actingAs($admin)->patch(route('admin.pemeriksaan-dokumen.approve', $document));
        $notification = $application->participant->notifications()->sole();

        $this->actingAs($application->participant, 'peserta')
            ->post(route('peserta.notifications.read', $notification->id))
            ->assertRedirect(route('peserta.dashboard').'#persiapan');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    /**
     * @return array{User, ParticipantApplicationDocument, ParticipantApplication}
     */
    private function reviewFixture(): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Aktif']);
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
            'status' => 'letter_under_review',
            'guestbook_confirmed_at' => now(),
            'letter_submitted_at' => now(),
        ]);
        $document = $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
            'version' => 1,
            'file_path' => 'participant-applications/test/request-letter.pdf',
            'original_name' => 'surat-permohonan.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
            'automated_check_status' => 'passed',
            'automated_check_results' => [
                'status' => 'passed',
                'summary' => 'Pemeriksaan awal selesai.',
                'checks' => [],
            ],
            'automated_checked_at' => now(),
        ]);

        return [$admin, $document, $application];
    }
}
