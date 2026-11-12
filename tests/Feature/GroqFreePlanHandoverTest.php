<?php

namespace Tests\Feature;

use App\Services\FreeGroqPolicy;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqFreePlanHandoverTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/groq-handover-test-'.bin2hex(random_bytes(10));
        File::ensureDirectoryExists($this->directory.'/bootstrap/cache');
        $this->app->useEnvironmentPath($this->directory)->loadEnvironmentFrom('.env');
        $this->app->useBootstrapPath($this->directory.'/bootstrap');
        File::put($this->directory.'/.env', "APP_NAME=Test\nGROQ_ENABLED=false\nGROQ_API_KEY=institution-test-key\n");
        config(['services.groq.api_key' => 'institution-test-key', 'services.groq.model' => FreeGroqPolicy::MODEL]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_handover_requires_three_explicit_operator_confirmations_and_binds_to_key(): void
    {
        Http::fake();
        $this->artisan('chatbot:confirm-free-plan')
            ->expectsConfirmation('Apakah akun Groq ini milik dinas, bukan akun pribadi?', 'yes')
            ->expectsConfirmation('Apakah akun ini pada Free Plan, tanpa paket billing berbayar?', 'yes')
            ->expectsConfirmation('Aktifkan Groq untuk akun dinas yang sudah Anda periksa ini?', 'yes')
            ->assertSuccessful();
        $env = File::get($this->directory.'/.env');
        $this->assertStringContainsString('GROQ_ENABLED=true', $env);
        $this->assertStringContainsString('GROQ_FREE_PLAN_KEY_SHA256='.hash('sha256', 'institution-test-key'), $env);
        $this->assertStringContainsString('APP_NAME=Test', $env);
        Http::assertNothingSent();
    }

    public function test_refusing_confirmation_leaves_configuration_disabled_and_revoke_clears_authorization(): void
    {
        $before = File::get($this->directory.'/.env');
        $this->artisan('chatbot:confirm-free-plan')
            ->expectsConfirmation('Apakah akun Groq ini milik dinas, bukan akun pribadi?', 'no')->assertFailed();
        $this->assertSame($before, File::get($this->directory.'/.env'));
        File::append($this->directory.'/.env', "GROQ_FREE_PLAN_CONFIRMED=true\nGROQ_FREE_PLAN_KEY_SHA256=old\n");
        $this->artisan('chatbot:confirm-free-plan', ['--revoke' => true])->assertSuccessful();
        $env = File::get($this->directory.'/.env');
        $this->assertStringContainsString('GROQ_ENABLED=false', $env);
        $this->assertStringContainsString('GROQ_FREE_PLAN_CONFIRMED=false', $env);
        $this->assertStringNotContainsString('GROQ_FREE_PLAN_KEY_SHA256=old', $env);
    }
}
