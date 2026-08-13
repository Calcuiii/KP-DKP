<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_nonactive_admin_cannot_log_in(): void
    {
        $admin = User::factory()->create([
            'email' => 'nonaktif@dkp.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'Nonaktif',
        ]);

        $this->post(route('admin-login.store'), [
            'email' => $admin->email,
            'password' => 'password123',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_nonactive_admin_cannot_access_admin_pages_with_an_existing_session(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'Nonaktif',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_an_active_admin_can_log_in(): void
    {
        $admin = User::factory()->create([
            'email' => 'aktif@dkp.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'Aktif',
        ]);

        $this->post(route('admin-login.store'), [
            'email' => $admin->email,
            'password' => 'password123',
        ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($admin->fresh()->last_login_at);
    }
}
