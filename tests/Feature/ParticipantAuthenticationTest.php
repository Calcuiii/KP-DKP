<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use App\Notifications\ParticipantVerifyEmail;
use App\Notifications\ParticipantResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
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
        Notification::assertSentTo($participant, ParticipantVerifyEmail::class);
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
            ->assertSee('Lupa Kata Sandi?')
            ->assertSee(route('peserta.password.request'), false)
            ->assertSee('Daftar Akun');

        $this->get(route('peserta.register'))
            ->assertOk()
            ->assertSee('Konfirmasi Kata Sandi')
            ->assertSee('tulis nama lengkap, bukan nama samaran atau nama panggilan')
            ->assertSee('Masukkan nama lengkap')
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

    public function test_a_participant_can_request_a_password_reset_link_without_disclosing_account_existence(): void
    {
        Notification::fake();
        $participant = Participant::factory()->create(['email' => 'peserta@example.test']);

        $message = 'Jika email terdaftar, tautan untuk mengatur ulang kata sandi akan segera dikirim. Silakan periksa kotak masuk dan folder spam.';

        $this->post(route('peserta.password.email'), ['email' => $participant->email])
            ->assertSessionHas('status', $message);
        $this->post(route('peserta.password.email'), ['email' => 'tidak-ada@example.test'])
            ->assertSessionHas('status', $message);

        Notification::assertSentTo($participant, ParticipantResetPassword::class);
    }

    public function test_a_participant_can_reset_their_password_with_a_valid_token(): void
    {
        $participant = Participant::factory()->create([
            'email' => 'peserta@example.test',
            'password' => Hash::make('password-lama'),
        ]);
        $token = Password::broker('participants')->createToken($participant);

        $this->post(route('peserta.password.update'), [
            'token' => $token,
            'email' => $participant->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect(route('peserta.login'))
            ->assertSessionHas('status', 'Kata sandi berhasil diperbarui. Silakan masuk menggunakan kata sandi baru.');

        $this->assertTrue(Hash::check('password-baru', $participant->fresh()->password));
    }

    public function test_an_invalid_password_reset_token_is_rejected(): void
    {
        $participant = Participant::factory()->create(['email' => 'peserta@example.test']);

        $this->post(route('peserta.password.update'), [
            'token' => 'token-tidak-valid',
            'email' => $participant->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertSessionHasErrors('email');
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

        Notification::assertSentTo($participant, ParticipantVerifyEmail::class);
    }

    public function test_the_participant_verification_email_uses_si_melayur_branding(): void
    {
        $participant = Participant::factory()->create([
            'name' => 'Peserta Uji',
            'email_verified_at' => null,
        ]);

        $message = (new ParticipantVerifyEmail)->toMail($participant);
        $html = $message->render();

        $this->assertSame('Verifikasi akun peserta SI-MELAYUR', $message->subject);
        $this->assertStringContainsString('SI-MELAYUR', $html);
        $this->assertStringContainsString('Selamat datang, Peserta Uji!', $html);
        $this->assertStringContainsString('Verifikasi Email Saya', $html);
        $this->assertStringNotContainsString('Laravel Logo', $html);
        $this->assertStringNotContainsString('Regards,<br>Laravel', $html);
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
