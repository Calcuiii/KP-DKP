<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use App\Models\ReplyLetter;
use App\Models\User;
use App\Notifications\ReplyLetterSent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ReplyLetterDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploading_a_reply_letter_delivers_it_to_the_portal_and_participant_email(): void
    {
        Storage::fake('public');
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'Aktif',
        ]);
        $participant = Participant::factory()->create([
            'email_verified_at' => now(),
        ]);
        $application = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
            'status' => 'response_pending',
            'google_form_confirmed_at' => now(),
        ]);
        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF,
            'version' => 1,
            'file_path' => 'participant-applications/form-proof.pdf',
            'original_name' => 'form-proof.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.surat-balasan.upload', $participant), [
                'decision' => 'accepted',
                'official_started_at' => '2026-09-07',
                'official_ended_at' => '2026-12-18',
                'reply_letter' => UploadedFile::fake()->create(
                    'surat-balasan-resmi.pdf',
                    250,
                    'application/pdf',
                ),
            ]);

        $response
            ->assertRedirect(route('admin.surat-balasan'))
            ->assertSessionHas('success');

        $replyLetter = ReplyLetter::query()
            ->whereBelongsTo($participant)
            ->firstOrFail();

        Storage::disk('public')->assertExists($replyLetter->file_path);

        $application->refresh();
        $this->assertSame('accepted', $application->decision);
        $this->assertSame('accepted', $application->status);
        $this->assertSame('2026-09-07', $application->official_started_at->toDateString());
        $this->assertSame('2026-12-18', $application->official_ended_at->toDateString());

        Notification::assertSentTo(
            $participant,
            ReplyLetterSent::class,
            function (ReplyLetterSent $notification, array $channels) use ($participant, $replyLetter): bool {
                $mail = $notification->toMail($participant);

                self::assertContains('database', $channels);
                self::assertContains('mail', $channels);
                self::assertSame(
                    ['html' => 'emails.reply-letter-sent', 'text' => 'emails.reply-letter-sent-text'],
                    $mail->view,
                );
                self::assertSame(
                    'surat-balasan-resmi.pdf',
                    $mail->attachments[0]['options']['as'] ?? null,
                );
                self::assertSame(
                    'application/pdf',
                    $mail->attachments[0]['options']['mime'] ?? null,
                );
                self::assertSame('accepted', $notification->toArray($participant)['status']);
                self::assertStringContainsString('07 September 2026', $notification->toArray($participant)['message']);
                self::assertStringContainsString(
                    'Surat balasan terlampir',
                    view('emails.reply-letter-sent-text', $mail->viewData)->render(),
                );

                return $replyLetter->is($participant->replyLetter);
            },
        );

        $this
            ->actingAs($participant, 'peserta')
            ->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee('Kalender kegiatan magang')
            ->assertSee($application->official_started_at->translatedFormat('d M Y'))
            ->assertSee($application->official_ended_at->translatedFormat('d M Y'));
    }

    public function test_rejected_decision_clears_the_official_period(): void
    {
        Storage::fake('public');
        Notification::fake();

        $admin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
            'status' => 'response_pending',
            'google_form_confirmed_at' => now(),
            'official_started_at' => '2026-09-07',
            'official_ended_at' => '2026-12-18',
        ]);
        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF,
            'version' => 1,
            'file_path' => 'participant-applications/form-proof.pdf',
            'original_name' => 'form-proof.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $this->actingAs($admin)->post(route('admin.surat-balasan.upload', $participant), [
            'decision' => 'rejected',
            'reply_letter' => UploadedFile::fake()->create('surat-penolakan.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('admin.surat-balasan'));

        $application->refresh();
        $this->assertSame('rejected', $application->decision);
        $this->assertSame('rejected', $application->status);
        $this->assertNull($application->official_started_at);
        $this->assertNull($application->official_ended_at);

        Notification::assertSentTo($participant, ReplyLetterSent::class, function (ReplyLetterSent $notification) use ($participant): bool {
            return $notification->toArray($participant)['status'] === 'rejected';
        });
    }
}
