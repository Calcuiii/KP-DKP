<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Contracts\Mail\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class HostingHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_never_creates_a_default_administrator(): void
    {
        $this->seed();
        $this->assertDatabaseCount('users', 0);
        $this->artisan('admin:provision', ['email' => 'operator@example.test', '--name' => 'Operator Uji'])
            ->expectsQuestion('Password unik (minimal 12 karakter, huruf besar/kecil dan angka)', 'StrongLocalTest123')
            ->expectsQuestion('Ulangi password', 'StrongLocalTest123')->assertSuccessful();
        $this->assertTrue(Hash::check('StrongLocalTest123', User::sole()->password));
        $this->artisan('admin:provision', ['email' => 'other@example.test'])->assertFailed();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_document_migration_preserves_verified_private_copy_and_backup_and_removes_public_url_source(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('public')->put('reply-letters/official.pdf', 'private contents');
        $this->artisan('documents:privatize', ['--force' => true])->assertSuccessful();
        Storage::disk('public')->assertMissing('reply-letters/official.pdf');
        $this->assertSame('private contents', Storage::disk('local')->get('reply-letters/official.pdf'));
        $backups = Storage::disk('local')->allFiles('migration-backups');
        $this->assertCount(1, $backups);
        $this->assertSame('private contents', Storage::disk('local')->get($backups[0]));
        $this->artisan('documents:privatize', ['--force' => true])->assertSuccessful();
        $this->assertCount(1, Storage::disk('local')->allFiles('migration-backups'));
    }

    public function test_migration_conflict_keeps_the_public_original_and_existing_private_file(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('public')->put('reply-letters/a.pdf', 'old');
        Storage::disk('local')->put('reply-letters/a.pdf', 'different');
        try {
            $this->artisan('documents:privatize', ['--force' => true])->run();
            $this->fail('Conflicting contents must never be overwritten.');
        } catch (\RuntimeException $e) {
            $this->assertSame('old', Storage::disk('public')->get('reply-letters/a.pdf'));
            $this->assertSame('different', Storage::disk('local')->get('reply-letters/a.pdf'));
        }
    }

    public function test_completed_or_downstream_stages_cannot_be_rewritten_by_direct_post(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create(['service_type' => 'magang_pkl', 'decision' => 'accepted',
            'status' => 'completed', 'completed_at' => now(), 'presentation_submitted_at' => now(),
            'completion_form_submitted_at' => now()]);
        $this->actingAs($participant, 'peserta')->post(route('peserta.presentation.store', $application), [
            'presentation_date' => now()->toDateString(), 'presentation_photo' => UploadedFile::fake()->image('new.jpg'),
        ])->assertUnprocessable();
        $this->post(route('peserta.completion-proof.store', $application), [
            'completion_form_proof' => UploadedFile::fake()->image('new.png'),
        ])->assertUnprocessable();
        $this->post(route('peserta.guestbook-proof.store'), ['application_id' => $application->id,
            'guestbook_proof' => UploadedFile::fake()->image('proof.png'), 'guestbook_declaration' => '1',
        ])->assertUnprocessable();
        $this->assertSame('completed', $application->fresh()->status);
        $this->assertEmpty(Storage::disk('local')->allFiles());
    }

    public function test_failed_certificate_email_does_not_claim_success_or_destroy_the_published_certificate(): void
    {
        Storage::fake('local');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create(['service_type' => 'magang_pkl', 'decision' => 'accepted',
            'presentation_submitted_at' => now(), 'completion_form_submitted_at' => now()]);
        $this->mock(Factory::class)->shouldReceive('mailer')->andThrow(new \RuntimeException('Simulated mail failure'));
        $admin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $this->actingAs($admin)->post(route('admin.sertifikat.kirim', $application), [
            'spreadsheet_verified' => '1', 'certificate' => UploadedFile::fake()->create('certificate.pdf', 10, 'application/pdf'),
        ])->assertRedirect()->assertSessionHas('error');
        $application->refresh();
        $this->assertNull($application->certificate_emailed_at);
        $this->assertNotNull($application->certificate_published_at);
        Storage::disk('local')->assertExists($application->certificate_path);
        $this->assertCount(1, $participant->notifications);
    }

    public function test_certificate_requires_an_accepted_internship_and_success_is_recorded_only_after_mail(): void
    {
        Storage::fake('local');
        Notification::fake();
        $participant = Participant::factory()->create();
        $application = $participant->applications()->create(['service_type' => 'wopps', 'decision' => 'accepted',
            'presentation_submitted_at' => now(), 'completion_form_submitted_at' => now()]);
        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->post(route('admin.sertifikat.kirim', $application), [
            'spreadsheet_verified' => '1', 'certificate' => UploadedFile::fake()->create('test.pdf', 10, 'application/pdf'),
        ])->assertUnprocessable();
        $application->update(['service_type' => 'magang_pkl']);
        $this->post(route('admin.sertifikat.kirim', $application), [
            'spreadsheet_verified' => '1', 'certificate' => UploadedFile::fake()->create('test.pdf', 10, 'application/pdf'),
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertNotNull($application->fresh()->certificate_emailed_at);
    }

    public function test_security_headers_and_private_response_caching(): void
    {
        $this->get(route('landing'))->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->actingAs(Participant::factory()->create(['email_verified_at' => now()]), 'peserta')
            ->get(route('peserta.dashboard'))->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_production_rejects_an_untrusted_host(): void
    {
        $this->app->instance('env', 'production');
        config(['app.url' => 'https://simelayur.example.test']);
        $this->get('https://simelayur.example.test/')->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $this->get('https://attacker.example.test/')->assertStatus(400);
    }

    public function test_history_session_key_can_be_sent_in_a_header_without_url_disclosure(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($participant, 'peserta')->withHeader('X-Chat-Session', (string) Str::uuid())
            ->getJson(route('chatbot.api.history'))->assertOk()->assertJsonPath('data', []);
    }
}
