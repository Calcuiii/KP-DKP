<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use App\Models\User;
use App\Notifications\ReplyLetterSent;
use App\Notifications\WoppsStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class WoppsDecisionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_accepted_wopps_moves_from_reply_letter_to_contact_and_completion(): void
    {
        Storage::fake('public');
        Notification::fake();

        $admin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        [$participant, $application] = $this->woppsApplication();

        $this->actingAs($admin)->post(route('admin.wopps-follow-up.decision', $application), [
            'decision' => 'accepted',
            'reply_letter' => UploadedFile::fake()->create('surat-wopps.pdf', 100, 'application/pdf'),
        ])->assertRedirect()->assertSessionHas('success');

        $application->refresh();
        $this->assertSame('accepted', $application->decision);
        $this->assertSame('wopps_waiting_contact', $application->status);
        $this->assertNull($application->official_started_at);
        $this->assertNull($application->official_ended_at);

        Notification::assertSentTo($participant, ReplyLetterSent::class, function (ReplyLetterSent $notification) use ($participant): bool {
            $data = $notification->toArray($participant);
            $mail = $notification->toMail($participant);

            return str_contains($data['message'], 'tunggu petugas Dinas menghubungi')
                && str_contains(view('emails.reply-letter-sent-text', $mail->viewData)->render(), 'Petugas Dinas akan menghubungi')
                && ! str_contains(view('emails.reply-letter-sent-text', $mail->viewData)->render(), 'Periode pelaksanaan:');
        });

        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee('Menunggu dihubungi pihak Dinas')
            ->assertSee('Anda tidak perlu menghubungi petugas terlebih dahulu')
            ->assertDontSee('Hubungi via WhatsApp');

        $this->actingAs($admin)->post(route('admin.wopps-follow-up.mark-contacted', $application))->assertRedirect();
        $this->assertSame('wopps_contacted', $application->fresh()->status);
        $this->assertNotNull($application->fresh()->pic_contacted_at);

        $this->actingAs($admin)->post(route('admin.wopps-follow-up.complete', $application))->assertRedirect();
        $this->assertSame('completed', $application->fresh()->status);
        $this->assertNotNull($application->fresh()->completed_at);
        Notification::assertSentTo($participant, WoppsStatusUpdated::class);

        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee('Layanan WOPPS selesai')
            ->assertSee('Ingin mengajukan layanan lagi?');

        $this->actingAs($participant, 'peserta')->post(route('peserta.application.store'), [
            'service_type' => ParticipantApplication::SERVICE_MAGANG_PKL,
        ])->assertRedirect(route('peserta.dashboard'));

        $this->assertSame(2, $participant->applications()->count());
        $this->assertSame(ParticipantApplication::SERVICE_MAGANG_PKL, $participant->applications()->latest()->first()->service_type);

        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee('Tahap belum tersedia')
            ->assertDontSee('Surat balasan telah dikirim');

        $this->actingAs($participant, 'peserta')->get(route('peserta.response-letter.download'))
            ->assertNotFound();
    }

    public function test_a_rejected_wopps_is_closed_without_dates_and_can_start_a_new_application(): void
    {
        Storage::fake('public');
        Notification::fake();

        $admin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        [$participant, $application] = $this->woppsApplication();

        $this->actingAs($admin)->post(route('admin.wopps-follow-up.decision', $application), [
            'decision' => 'rejected',
            'reply_letter' => UploadedFile::fake()->create('surat-penolakan.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $application->refresh();
        $this->assertSame('rejected', $application->status);
        $this->assertNull($application->official_started_at);
        $this->assertNull($application->official_ended_at);

        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))
            ->assertSee('Pengajuan WOPPS ini telah ditutup')
            ->assertSee('Ingin mengajukan layanan lagi?');

        $this->actingAs($participant, 'peserta')->post(route('peserta.application.store'), [
            'service_type' => ParticipantApplication::SERVICE_WOPPS,
        ])->assertRedirect(route('peserta.dashboard'));

        $this->assertSame(2, $participant->applications()->count());
    }

    /** @return array{Participant, ParticipantApplication} */
    private function woppsApplication(): array
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
            'file_path' => 'test/wopps-proof.png',
            'original_name' => 'wopps-proof.png',
            'mime_type' => 'image/png',
            'file_size' => 100,
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        return [$participant, $application];
    }
}
