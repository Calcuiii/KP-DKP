<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_mark_a_notification_as_read(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'Aktif',
        ]);
        $notification = $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'Tests\\AdminNotification',
            'data' => [
                'action_url' => route('admin.dashboard'),
            ],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.notifications.read', $notification))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
