<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_access_only_the_operational_admin_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Conversation Logs')
            ->assertSee('Pertanyaan Tidak Terjawab')
            ->assertSee('Analytics')
            ->assertSee('Activity Log')
            ->assertDontSee('Admin Login')
            ->assertSee('RINGKASAN LAYANAN')
            ->assertDontSee('Feedback Positif')
            ->assertSee(route('admin.unanswered-questions'))
            ->assertDontSee(route('admin.knowledge-base'))
            ->assertDontSee(route('admin.infographics'))
            ->assertDontSee(route('admin.manajemen-admin'));

        foreach ([
            'admin.conversation-logs',
            'admin.unanswered-questions',
            'admin.analytics',
            'admin.activity-log',
        ] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        foreach ([
            'admin.knowledge-base',
            'admin.infographics',
            'admin.manajemen-admin',
        ] as $route) {
            $this->actingAs($admin)->get(route($route))->assertForbidden();
        }
    }

    public function test_a_super_admin_can_access_all_admin_features(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($superAdmin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Admin Login')
            ->assertSee(route('admin.knowledge-base'))
            ->assertSee(route('admin.infographics'))
            ->assertSee(route('admin.manajemen-admin'));

        foreach ([
            'admin.knowledge-base',
            'admin.infographics',
            'admin.conversation-logs',
            'admin.unanswered-questions',
            'admin.analytics',
            'admin.manajemen-admin',
            'admin.activity-log',
        ] as $route) {
            $this->actingAs($superAdmin)->get(route($route))->assertOk();
        }
    }

    public function test_the_database_allows_only_one_super_admin(): void
    {
        User::factory()->create(['role' => 'superadmin']);

        $this->expectException(QueryException::class);

        User::factory()->create(['role' => 'superadmin']);
    }

    public function test_a_super_admin_can_only_create_regular_admin_accounts(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($superAdmin)
            ->post(route('admin.manajemen-admin.store'), [
                'name' => 'Admin Operasional',
                'email' => 'operasional@dkp.test',
                'password' => 'password123',
            ])
            ->assertRedirect(route('admin.manajemen-admin'));

        $this->assertDatabaseHas('users', [
            'email' => 'operasional@dkp.test',
            'role' => 'admin',
            'super_admin_slot' => null,
        ]);
    }
}
