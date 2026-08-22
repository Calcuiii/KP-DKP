<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\UnansweredEscalation;
use App\Models\User;
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
            ->assertSee('Halo, selamat datang di Asisten SI-MELAYUR!')
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

    public function test_the_chat_message_endpoint_returns_the_required_magang_submission_documents(): void
    {
        foreach ([
            'Apa saja persyaratan pengajuan magang?',
            'dokumen apa saja yang harus di persiapkan untuk pengajuan magang?',
            'Dokumen apa yang diperlukan untuk mengajukan KP?',
            'Apa saja syarat mendaftar PKL?',
        ] as $question) {
            $response = $this
                ->withCookie('dkp_guestbook_completed', '1')
                ->withCredentials()
                ->postJson(route('chatbot.api.messages.send'), [
                    'session_key' => (string) Str::uuid(),
                    'message' => $question,
                ]);

            $response
                ->assertCreated()
                ->assertJsonPath('data.message.status', 'success')
                ->assertJsonPath('data.message.sources.1.document_id', 'KB-007');

            foreach ([
                'pas foto peserta',
                'KTM untuk mahasiswa',
                'Kartu Pelajar untuk siswa',
                'Ethical Clearance',
                'Surat Permohonan',
                'Surat Kesehatan resmi',
            ] as $requiredDocument) {
                self::assertStringContainsString(
                    $requiredDocument,
                    $response->json('data.message.content'),
                    $question,
                );
            }
        }
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

    public function test_an_unanswered_question_can_be_forwarded_to_an_admin_once(): void
    {
        config(['services.whatsapp.enabled' => false]);

        $sessionKey = (string) Str::uuid();
        $conversation = ChatConversation::create([
            'session_key' => $sessionKey,
            'title' => 'Pertanyaan khusus',
            'last_message_at' => now(),
        ]);
        $userMessage = ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_USER,
            'content' => 'Apakah peserta mendapat fasilitas khusus?',
            'status' => 'submitted',
        ]);
        $assistantMessage = ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_ASSISTANT,
            'content' => 'Informasi belum ditemukan.',
            'status' => 'insufficient_information',
        ]);

        $route = route('chatbot.api.messages.escalate', $assistantMessage);
        $payload = ['session_key' => $sessionKey];

        $this->withCookie('dkp_guestbook_completed', '1')->withCredentials()->postJson($route, $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.whatsapp_status', 'skipped');

        $this->withCookie('dkp_guestbook_completed', '1')->withCredentials()->postJson($route, $payload)
            ->assertOk();

        $this->assertDatabaseCount('unanswered_escalations', 1);
        $this->assertDatabaseHas('unanswered_escalations', [
            'assistant_message_id' => $assistantMessage->id,
            'user_message_id' => $userMessage->id,
            'status' => 'new',
        ]);
    }

    public function test_an_admin_can_answer_a_forwarded_question_in_the_users_chat_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Aktif']);
        $sessionKey = (string) Str::uuid();
        $conversation = ChatConversation::create(['session_key' => $sessionKey, 'title' => 'Pertanyaan', 'last_message_at' => now()]);
        $userMessage = ChatMessage::create(['chat_conversation_id' => $conversation->id, 'role' => 'user', 'content' => 'Pertanyaan untuk petugas', 'status' => 'submitted']);
        $assistantMessage = ChatMessage::create(['chat_conversation_id' => $conversation->id, 'role' => 'assistant', 'content' => 'Belum ditemukan', 'status' => 'insufficient_information']);
        $escalation = UnansweredEscalation::create([
            'assistant_message_id' => $assistantMessage->id,
            'user_message_id' => $userMessage->id,
            'ticket_code' => 'SM-20260815-ABC123',
            'status' => 'new',
            'whatsapp_status' => 'skipped',
        ]);

        $this->actingAs($admin)->get(route('admin.unanswered-questions'))
            ->assertOk()
            ->assertSee('SM-20260815-ABC123')
            ->assertSee('Pertanyaan untuk petugas');

        $this->actingAs($admin)->get(route('admin.unanswered-questions.show', $escalation))
            ->assertOk()
            ->assertSee('Konteks percakapan')
            ->assertSee('Kirim jawaban dan selesaikan');

        $answer = 'Buku Tamu Magang dan PKL diisi melalui formulir resmi yang tersedia pada Portal Peserta.';

        $this->actingAs($admin)->post(route('admin.unanswered-questions.respond', $escalation), [
            'response' => $answer,
        ])->assertRedirect(route('admin.unanswered-questions.show', $escalation));

        $this->assertDatabaseHas('unanswered_escalations', [
            'id' => $escalation->id,
            'status' => 'resolved',
            'resolved_by' => $admin->id,
            'admin_response' => $answer,
        ]);
        $this->assertDatabaseHas('chat_messages', [
            'chat_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'status' => 'admin_answer',
            'content' => $answer,
        ]);

        $this->withCookie('dkp_guestbook_completed', '1')->withCredentials()
            ->getJson(route('chatbot.api.conversation', [
                'conversation' => $conversation,
                'session_key' => $sessionKey,
            ]))
            ->assertOk()
            ->assertJsonFragment([
                'content' => $answer,
                'status' => 'admin_answer',
            ])
            ->assertJsonFragment(['ticket_code' => 'SM-20260815-ABC123']);
    }
}
