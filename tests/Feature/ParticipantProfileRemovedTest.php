<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantProfileRemovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_menu_and_endpoints_are_removed(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))->assertOk()->assertDontSee('My Profile');
        $this->get('/peserta/profil')->assertNotFound();
        $this->patch('/peserta/profil', ['name' => 'Changed'])->assertNotFound();
        $this->patch('/peserta/profil/kata-sandi')->assertNotFound();
        $this->assertSame($participant->name, $participant->fresh()->name);
    }
}
