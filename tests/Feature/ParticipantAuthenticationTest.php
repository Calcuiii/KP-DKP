<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ParticipantAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_participant_can_register_with_valid_details(): void
    {
        Notification::fake();

        $response = $this->post(route('peserta.register.store'), [
            'name' => 'Calon Peserta',
            'email' => 'peserta@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'Akun berhasil dibuat. Periksa email Anda untuk memverifikasi akun.');

        $participant = Participant::query()->where('email', 'peserta@example.test')->firstOrFail();

        $this->assertTrue(Hash::check('password123', $participant->password));
        $this->assertDatabaseMissing('users', ['email' => $participant->email]);
        $this->assertAuthenticatedAs($participant, 'peserta');
        Notification::assertSentTo($participant, VerifyEmail::class);
    }

    public function test_registration_validates_the_input_and_unique_participant_email(): void
    {
        Participant::factory()->create(['email' => 'sudah-ada@example.test']);

        $this->post(route('peserta.register.store'), [
            'name' => '',
            'email' => 'sudah-ada@example.test',
            'password' => 'singkat',
            'password_confirmation' => 'berbeda',
        ])->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_a_participant_can_log_in_without_authenticating_the_web_guard(): void
    {
        $participant = Participant::factory()->create([
            'email' => 'peserta@example.test',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('peserta.login.store'), [
            'email' => $participant->email,
            'password' => 'password123',
        ])->assertRedirect(route('peserta.dashboard'));

        $this->assertAuthenticatedAs($participant, 'peserta');
        $this->assertGuest('web');
    }

    public function test_a_participant_cannot_log_in_with_invalid_credentials(): void
    {
        $participant = Participant::factory()->create([
            'email' => 'peserta@example.test',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('peserta.login.store'), [
            'email' => $participant->email,
            'password' => 'password-salah',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('peserta');
    }

    public function test_a_participant_can_log_out(): void
    {
        $participant = Participant::factory()->create();

        $this->actingAs($participant, 'peserta')
            ->post(route('peserta.logout'))
            ->assertRedirect(route('peserta.login'));

        $this->assertGuest('peserta');
    }

    public function test_participant_authentication_pages_and_dashboard_are_available(): void
    {
        $this->get(route('peserta.login'))
            ->assertOk()
            ->assertSee('Lupa Kata Sandi? Segera hadir')
            ->assertSee('Daftar Akun');

        $this->get(route('peserta.register'))
            ->assertOk()
            ->assertSee('Konfirmasi Kata Sandi')
            ->assertSee('Sudah memiliki akun?');

        $participant = Participant::factory()->create([
            'name' => 'Calon Peserta',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($participant, 'peserta')
            ->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee('Halo, Calon Peserta')
            ->assertSee('Pilih layanan yang akan dipersiapkan');
    }

    public function test_the_landing_page_provides_participant_login_and_registration_access(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee(route('peserta.login'), false)
            ->assertSee(route('peserta.register'), false)
            ->assertSee(route('admin.login'), false)
            ->assertSee('Daftar akun peserta');
    }

    public function test_the_landing_page_shows_the_participant_dashboard_link_after_login(): void
    {
        $participant = Participant::factory()->create();

        $this->actingAs($participant, 'peserta')
            ->get(route('landing'))
            ->assertOk()
            ->assertSee(route('peserta.dashboard'), false)
            ->assertSee('Dashboard Saya')
            ->assertDontSee('Daftar akun peserta');
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $participant = Participant::factory()->create([
            'email' => 'peserta@example.test',
            'password' => Hash::make('password123'),
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('peserta.login.store'), [
                'email' => $participant->email,
                'password' => "password-salah-{$attempt}",
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('peserta.login.store'), [
            'email' => $participant->email,
            'password' => 'password-salah-terakhir',
        ])->assertSessionHasErrors('email');
    }

    public function test_registration_is_rate_limited_after_three_attempts_from_one_ip_address(): void
    {
        foreach (range(1, 3) as $attempt) {
            $this->post(route('peserta.register.store'), [
                'name' => '',
                'email' => "peserta-{$attempt}@example.test",
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertSessionHasErrors('name');
        }

        $this->post(route('peserta.register.store'), [
            'name' => 'Peserta Keempat',
            'email' => 'peserta-4@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('participants', ['email' => 'peserta-4@example.test']);
    }

    public function test_an_unverified_participant_cannot_access_the_dashboard(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => null]);

        $this->actingAs($participant, 'peserta')
            ->get(route('peserta.dashboard'))
            ->assertRedirect(route('verification.notice'));

        $this->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Verifikasi Email')
            ->assertSee($participant->email);
    }

    public function test_a_participant_can_verify_their_email_with_a_signed_link(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => null]);
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $participant->getKey(),
                'hash' => sha1($participant->getEmailForVerification()),
            ],
        );

        $this->actingAs($participant, 'peserta')
            ->get($verificationUrl)
            ->assertRedirect(route('verification.notice'));

        $this->assertTrue($participant->fresh()->hasVerifiedEmail());

        $this->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Email Anda sudah terverifikasi')
            ->assertSee(route('peserta.dashboard'), false);
    }

    public function test_an_unverified_participant_can_request_another_verification_email(): void
    {
        Notification::fake();
        $participant = Participant::factory()->create(['email_verified_at' => null]);

        $this->actingAs($participant, 'peserta')
            ->post(route('verification.send'))
            ->assertSessionHas('status', 'Tautan verifikasi baru telah dikirim ke email Anda.');

        Notification::assertSentTo($participant, VerifyEmail::class);
    }

    public function test_a_participant_cannot_access_administrator_pages(): void
    {
        $participant = Participant::factory()->create();

        $this->actingAs($participant, 'peserta')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_an_administrator_cannot_access_participant_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('peserta.dashboard'))
            ->assertRedirect(route('peserta.login'));
    }
}
