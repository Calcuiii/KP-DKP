<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\VerifyAttendanceParticipantController;
use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-only-attendance-integration-token-123456';

    protected function setUp(): void
    {
        parent::setUp();
        config(['attendance.token_hash' => hash('sha256', $this->token), 'attendance.can_verify' => true]);
    }

    private function verify(array $body = ['email' => 'sample@example.test'])
    {
        return $this->withToken($this->token)->postJson('/api/participants/verify', $body);
    }

    private function application(): ParticipantApplication
    {
        $participant = Participant::factory()->create(['email' => 'sample@example.test', 'name' => 'Contoh Peserta']);
        $application = $participant->applications()->create([
            'service_type' => 'magang_pkl', 'status' => 'accepted', 'decision' => 'accepted',
            'official_started_at' => '2026-10-01', 'official_ended_at' => '2026-12-31',
        ]);
        $application->forceFill(['attendance_active' => true])->save();

        return $application;
    }

    public function test_service_authentication_rotation_revocation_and_permissions(): void
    {
        $this->postJson('/api/participants/verify')->assertUnauthorized();
        $this->withToken('invalid')->postJson('/api/participants/verify')->assertUnauthorized();
        config(['attendance.can_verify' => false]);
        $this->verify()->assertForbidden();
        config(['attendance.can_verify' => true, 'attendance.token_hash' => null, 'attendance.previous_token_hash' => hash('sha256', $this->token)]);
        $this->verify()->assertNotFound();
        config(['attendance.previous_token_hash' => null]);
        $this->verify()->assertUnauthorized();
    }

    public function test_validation_missing_participant_and_wopps_only(): void
    {
        foreach ([[], ['email' => []], ['email' => 'not-email']] as $body) {
            $this->verify($body)->assertUnprocessable()->assertJsonPath('error.code', 'INVALID_EMAIL');
        }
        $this->verify()->assertNotFound();
        $application = $this->application();
        $application->update(['service_type' => 'wopps']);
        $this->verify()->assertNotFound()->assertJsonPath('error.code', 'INTERNSHIP_APPLICATION_NOT_FOUND');
    }

    public function test_exact_contract_normalization_and_no_database_mutation(): void
    {
        $application = $this->application();
        $before = DB::table('participant_applications')->get()->toJson();
        $beforeParticipants = DB::table('participants')->get()->toJson();
        $this->verify(['email' => ' SAMPLE@EXAMPLE.TEST '])->assertOk()->assertExactJson(['participant' => [
            'id' => (string) $application->id, 'email' => 'sample@example.test', 'full_name' => 'Contoh Peserta',
            'status' => 'accepted', 'is_active' => true, 'start_date' => '2026-10-01', 'end_date' => '2026-12-31',
        ]])->assertHeader('Cache-Control', 'no-store, private');
        $this->assertSame($before, DB::table('participant_applications')->get()->toJson());
        $this->assertSame($beforeParticipants, DB::table('participants')->get()->toJson());
    }

    public function test_admission_is_distinct_from_administrative_activity(): void
    {
        $application = $this->application();
        foreach (['rejected' => 'rejected', '' => 'pending'] as $decision => $status) {
            $application->update(['decision' => $decision]);
            $this->verify()->assertOk()->assertJsonPath('participant.status', $status)->assertJsonPath('participant.is_active', false);
        }
        $application->update(['decision' => 'accepted']);
        $application->forceFill(['attendance_active' => false])->save();
        $this->verify()->assertOk()->assertJsonPath('participant.status', 'accepted')->assertJsonPath('participant.is_active', false);
        $application->forceFill(['attendance_active' => true, 'completed_at' => now()])->save();
        $this->verify()->assertOk()->assertJsonPath('participant.is_active', false);
    }

    public function test_period_is_returned_unchanged_before_during_and_after_inclusive_boundaries(): void
    {
        $this->application();
        foreach (['2026-09-30', '2026-10-01', '2026-11-08', '2026-12-31', '2027-01-01'] as $day) {
            $this->travelTo(Carbon::parse($day, 'Asia/Jakarta'));
            $this->verify()->assertOk()->assertJsonPath('participant.is_active', true)
                ->assertJsonPath('participant.start_date', '2026-10-01')->assertJsonPath('participant.end_date', '2026-12-31');
        }
    }

    public function test_ambiguous_applications_never_choose_latest_or_current_period(): void
    {
        $application = $this->application();
        $application->participant->applications()->create(['service_type' => 'wopps', 'status' => 'preparation']);
        $this->verify()->assertOk();
        $application->participant->applications()->create(['service_type' => 'magang_pkl', 'status' => 'preparation']);
        $this->verify()->assertStatus(409)->assertJsonPath('error.code', 'AMBIGUOUS_APPLICATION');
    }

    public function test_missing_or_invalid_official_data_fails_closed(): void
    {
        $application = $this->application();
        $application->forceFill(['attendance_active' => null])->save();
        $this->verify()->assertStatus(409)->assertJsonMissingPath('participant')->assertJsonPath('error.code', 'PARTICIPANT_DATA_INCOMPLETE');
        $application->forceFill(['attendance_active' => true, 'official_started_at' => null])->save();
        $this->verify()->assertStatus(409);
        $application->update(['official_started_at' => '2026-10-01', 'official_ended_at' => null]);
        $this->verify()->assertStatus(409);
        $application->update(['official_ended_at' => '2026-12-31']);
        $application->update(['official_started_at' => '2027-01-01']);
        $this->verify()->assertStatus(409)->assertJsonPath('error.code', 'INVALID_PERIOD');
        $application->update(['decision' => 'unknown']);
        $this->verify()->assertStatus(409)->assertJsonPath('error.code', 'UNSUPPORTED_DECISION');
    }

    public function test_rate_limit_and_internal_errors_do_not_leak_details(): void
    {
        config(['app.debug' => true]);
        $this->app->bind(VerifyAttendanceParticipantController::class, function () {
            throw new \RuntimeException('secret-sql-email-token');
        });
        $this->verify()->assertStatus(500)->assertExactJson(['error' => [
            'code' => 'SERVICE_UNAVAILABLE', 'message' => 'Request could not be processed.',
        ]]);
    }

    public function test_requests_are_rate_limited(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->verify()->assertNotFound();
        }
        $this->verify()->assertStatus(429)->assertJsonPath('error.code', 'RATE_LIMITED')->assertHeader('Retry-After');
    }

    public function test_production_requires_https_and_does_not_accept_browser_session_as_service_auth(): void
    {
        $this->app->instance('env', 'production');
        $this->verify()->assertForbidden()->assertJsonPath('error.code', 'HTTPS_REQUIRED');
        $this->withToken($this->token)->postJson('https://'.parse_url(config('app.url'), PHP_URL_HOST).'/api/participants/verify', ['email' => 'missing@example.test'])->assertNotFound();
        $this->app->instance('env', 'testing');
        $this->flushHeaders();
        $participant = Participant::factory()->create();
        $this->actingAs($participant, 'peserta')->postJson('/api/participants/verify', ['email' => $participant->email])->assertUnauthorized();
    }

    public function test_administrator_command_sets_and_revokes_activity_with_audit(): void
    {
        $application = $this->application();
        $admin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $args = ['application' => $application->id, '--operator' => $admin->id, '--force' => true];
        foreach (['inactive' => false, 'active' => true, 'unset' => null] as $state => $expected) {
            $this->artisan('attendance:set-active', $args + ['state' => $state])->assertExitCode(0);
            $this->assertSame($expected, $application->fresh()->attendance_active);
        }
        $this->assertDatabaseCount('activity_logs', 3);
        $application->update(['decision' => 'rejected']);
        $this->artisan('attendance:set-active', $args + ['state' => 'active'])->assertExitCode(1);
        $this->assertNull($application->fresh()->attendance_active);
    }

    public function test_case_variant_duplicate_emails_are_ambiguous_and_legacy_case_is_matched(): void
    {
        $application = $this->application();
        $application->participant->update(['email' => 'SAMPLE@example.test']);
        $this->verify()->assertOk();
        Participant::factory()->create(['email' => 'sample@example.test']);
        $this->verify()->assertStatus(409)->assertJsonPath('error.code', 'AMBIGUOUS_PARTICIPANT');
    }
}
