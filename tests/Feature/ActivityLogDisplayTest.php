<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActivityLogDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_activity_times_in_surabaya_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = ActivityLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'Login',
            'module' => 'Auth',
            'description' => 'Login ke sistem admin',
            'ip_address' => '203.0.113.42',
        ]);
        $log->forceFill([
            'created_at' => CarbonImmutable::parse('2026-08-04 12:28:00', 'UTC'),
            'updated_at' => CarbonImmutable::parse('2026-08-04 12:28:00', 'UTC'),
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.activity-log'))
            ->assertOk()
            ->assertSee('2026-08-04 19:28 WIB')
            ->assertSee('203.0.113.42');
    }

    public function test_it_records_the_client_ip_forwarded_by_a_trusted_local_proxy(): void
    {
        $admin = User::factory()->create([
            'email' => 'ip-admin@dkp.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.42',
        ])->post(route('admin-login.store'), [
            'email' => $admin->email,
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'ip_address' => '203.0.113.42',
        ]);
    }
}
