<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConversationLogFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_logs_by_the_status_of_each_questions_own_answer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $conversation = ChatConversation::query()->create([
            'session_key' => (string) Str::uuid(),
            'title' => 'Percakapan pengujian',
            'last_message_at' => now(),
        ]);

        $this->createQuestionAndAnswer(
            $conversation,
            'Pertanyaan yang dijawab',
            'Jawaban tersedia',
            'success',
        );
        $this->createQuestionAndAnswer(
            $conversation,
            'Pertanyaan yang tidak ditemukan',
            'Informasi tidak tersedia',
            'insufficient_information',
        );

        $this->actingAs($admin)
            ->get(route('admin.conversation-logs', ['status' => 'Tidak Ditemukan']))
            ->assertOk()
            ->assertSee('Pertanyaan yang tidak ditemukan')
            ->assertSee('Tidak Ditemukan')
            ->assertDontSee('Pertanyaan yang dijawab')
            ->assertDontSee('Jawaban tersedia');
    }

    public function test_it_displays_and_exports_conversation_dates_in_surabaya_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $conversation = ChatConversation::query()->create([
            'session_key' => (string) Str::uuid(),
            'title' => 'Percakapan waktu',
            'last_message_at' => CarbonImmutable::parse('2026-08-04 12:29:00', 'UTC'),
        ]);

        $question = ChatMessage::query()->create([
            'chat_conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_USER,
            'content' => 'Pertanyaan dengan waktu WIB',
            'status' => 'submitted',
        ]);
        $question->forceFill([
            'created_at' => CarbonImmutable::parse('2026-08-04 12:29:00', 'UTC'),
            'updated_at' => CarbonImmutable::parse('2026-08-04 12:29:00', 'UTC'),
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.conversation-logs'))
            ->assertOk()
            ->assertSee('2026-08-04 19:29 WIB');

        $this->actingAs($admin)
            ->get(route('admin.conversation-logs.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertSee('Tanggal (WIB)')
            ->assertSee('2026-08-04 19:29 WIB');
    }

    private function createQuestionAndAnswer(
        ChatConversation $conversation,
        string $question,
        string $answer,
        string $status,
    ): void {
        ChatMessage::query()->create([
            'chat_conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_USER,
            'content' => $question,
            'status' => 'submitted',
        ]);

        ChatMessage::query()->create([
            'chat_conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_ASSISTANT,
            'content' => $answer,
            'status' => $status,
        ]);
    }
}
