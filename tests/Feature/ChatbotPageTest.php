<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatbotPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_chatbot_page_renders_successfully(): void
    {
        $response = $this
            ->withCookie('dkp_guestbook_completed', '1')
            ->get(route('chatbot'));

        $response
            ->assertOk()
            ->assertSee('data-chatbot-app', false)
            ->assertSee('data-chat-message-list', false)
            ->assertSee('Halo, selamat datang di DKP Assistant!')
            ->assertSee('Magang / PKL')
            ->assertSee('WOPPS')
            ->assertDontSee('fixed bottom-6 left-1/2', false);
    }

    public function test_the_chat_message_endpoint_uses_its_form_request(): void
    {
        $response = $this
            ->withCookie('dkp_guestbook_completed', '1')
            ->withCredentials()
            ->postJson(route('chatbot.api.messages.send'));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'session_key',
                'message',
            ]);
    }

    public function test_the_chat_message_endpoint_returns_the_approved_answer_for_a_magang_quick_question(): void
    {
        $response = $this
            ->withCookie('dkp_guestbook_completed', '1')
            ->withCredentials()
            ->postJson(route('chatbot.api.messages.send'), [
                'session_key' => (string) Str::uuid(),
                'message' => 'Bagaimana alur pengajuan Magang / PKL?',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.message.status', 'success')
            ->assertJsonPath('data.message.sources.0.document_id', 'KB-007');

        self::assertStringContainsString(
            '1. Isi Buku Tamu Magang / PKL.',
            $response->json('data.message.content'),
        );
        self::assertStringContainsString(
            '11. Proses dokumen akhir dilakukan sesuai ketentuan yang berlaku.',
            $response->json('data.message.content'),
        );
    }

    public function test_the_chat_message_endpoint_returns_the_approved_answer_for_a_wopps_quick_question(): void
    {
        $response = $this
            ->withCookie('dkp_guestbook_completed', '1')
            ->withCredentials()
            ->postJson(route('chatbot.api.messages.send'), [
                'session_key' => (string) Str::uuid(),
                'message' => 'Ke mana saya mengirimkan dokumen pengajuan?',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.message.status', 'success')
            ->assertJsonPath('data.message.sources.0.document_id', 'KB-009');

        self::assertStringContainsString(
            'https://bit.ly/WOPPS',
            $response->json('data.message.content'),
        );
        self::assertStringContainsString(
            '0852-53000-485',
            $response->json('data.message.content'),
        );
    }

    public function test_the_chat_message_endpoint_uses_the_conversation_question_for_an_explicit_follow_up(): void
    {
        config(['services.groq.enabled' => false]);

        $sessionKey = (string) Str::uuid();
        $conversation = ChatConversation::query()->create([
            'session_key' => $sessionKey,
            'title' => 'Apa saja persyaratan pengajuan magang?',
            'last_message_at' => now(),
        ]);
        ChatMessage::query()->create([
            'chat_conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_USER,
            'content' => 'Apa saja persyaratan pengajuan magang?',
            'status' => 'submitted',
        ]);

        $response = $this
            ->withCookie('dkp_guestbook_completed', '1')
            ->withCredentials()
            ->postJson(route('chatbot.api.messages.send'), [
                'session_key' => $sessionKey,
                'conversation_id' => $conversation->id,
                'message' => 'Apakah hanya itu?',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.message.status', 'success')
            ->assertJsonPath('data.conversation.id', $conversation->id)
            ->assertSeeText('Kompetensi Keahlian')
            ->assertSeeText('Jumlah Peserta');
    }
}
