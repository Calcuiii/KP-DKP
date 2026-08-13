<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantGuardConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_participants_are_stored_separately_from_administrator_users(): void
    {
        $participant = Participant::factory()->create();

        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'email' => $participant->email,
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => $participant->email,
        ]);
    }

    public function test_the_peserta_guard_uses_the_participant_provider(): void
    {
        $this->assertSame('participants', config('auth.guards.peserta.provider'));
        $this->assertSame(Participant::class, config('auth.providers.participants.model'));
        $this->assertSame(User::class, config('auth.providers.users.model'));
    }
}
