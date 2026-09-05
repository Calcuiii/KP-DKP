<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ChatHistoryRequest;
use App\Http\Requests\SendChatMessageRequest;
use App\Http\Requests\StoreChatFeedbackRequest;
use App\Models\ChatConversation;
use App\Models\ChatFeedback;
use App\Models\ChatMessage;
use App\Models\UnansweredEscalation;
use App\Models\User;
use App\Notifications\UnansweredQuestionEscalated;
use App\Services\GroundedChatbotResponder;
use App\Services\WhatsAppAdminNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

final class ChatbotController extends Controller
{
    public function index(): View
    {
        return view('pages.chatbot');
    }

    public function history(ChatHistoryRequest $request): JsonResponse
    {
        $sessionKey = $request->validated('session_key');

        $conversations = ChatConversation::query()
            ->where('session_key', $sessionKey)
            ->orderByDesc('last_message_at')
            ->limit(50)
            ->get(['id', 'title', 'last_message_at', 'created_at']);

        return response()->json([
            'data' => $conversations->map(fn (ChatConversation $conversation): array => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'last_message_at' => $conversation->last_message_at?->toIso8601String(),
                'created_at' => $conversation->created_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    public function conversation(
        ChatHistoryRequest $request,
        ChatConversation $conversation,
    ): JsonResponse {
        $sessionKey = $request->validated('session_key');

        abort_unless(hash_equals($conversation->session_key, $sessionKey), 404);

        $conversation->load([
            'messages.sources',
            'messages.feedback',
        ]);

        return response()->json([
            'data' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'messages' => $conversation->messages->map(
                    fn (ChatMessage $message): array => $this->serializeMessage($message),
                )->values(),
            ],
        ]);
    }

    public function send(
        SendChatMessageRequest $request,
        GroundedChatbotResponder $responder,
    ): JsonResponse {
        $validated = $request->validated();
        $startedAt = hrtime(true);

        try {
            $existingConversation = $this->existingConversation(
                $validated['session_key'],
                $validated['conversation_id'] ?? null,
            );
            $result = $responder->answer(
                $validated['message'],
                $this->previousUserQuestion($existingConversation),
            );

            $payload = DB::transaction(function () use ($validated, $result, $startedAt): array {
                $conversation = $this->resolveConversation(
                    $validated['session_key'],
                    $validated['conversation_id'] ?? null,
                    $validated['message'],
                );

                ChatMessage::query()->create([
                    'chat_conversation_id' => $conversation->id,
                    'role' => ChatMessage::ROLE_USER,
                    'content' => $validated['message'],
                    'status' => 'submitted',
                    'response_time_ms' => null,
                ]);

                $responseTimeMs = (int) round((hrtime(true) - $startedAt) / 1_000_000);

                $assistantMessage = ChatMessage::query()->create([
                    'chat_conversation_id' => $conversation->id,
                    'role' => ChatMessage::ROLE_ASSISTANT,
                    'content' => $result['answer'],
                    'status' => $result['status'],
                    'response_time_ms' => $responseTimeMs,
                ]);

                foreach ($result['sources'] as $position => $source) {
                    $assistantMessage->sources()->create([
                        'document_id' => $source['document_id'],
                        'document_title' => $source['document_title'],
                        'section_title' => $source['section_title'],
                        'position' => $position + 1,
                    ]);
                }

                $conversation->forceFill([
                    'last_message_at' => now(),
                ])->save();

                $assistantMessage->load('sources');

                return [
                    'conversation' => [
                        'id' => $conversation->id,
                        'title' => $conversation->title,
                    ],
                    'message' => $this->serializeMessage($assistantMessage),
                ];
            });

            return response()->json(['data' => $payload], 201);
        } catch (Throwable $exception) {
            Log::error('Chatbot request failed.', [
                'exception' => $exception,
                'session_key' => $validated['session_key'],
            ]);

            return response()->json([
                'message' => 'Layanan chatbot sedang mengalami kendala. Silakan coba kembali beberapa saat lagi.',
            ], 500);
        }
    }

    public function feedback(
        StoreChatFeedbackRequest $request,
        ChatMessage $message,
    ): JsonResponse {
        $validated = $request->validated();

        $message->loadMissing('conversation');

        abort_unless(
            $message->role === ChatMessage::ROLE_ASSISTANT
            && hash_equals($message->conversation->session_key, $validated['session_key']),
            404,
        );

        $feedback = ChatFeedback::query()->updateOrCreate(
            ['chat_message_id' => $message->id],
            [
                'rating' => $validated['rating'],
                'reason' => $validated['reason'] ?? null,
            ],
        );

        return response()->json([
            'data' => [
                'message_id' => $message->id,
                'rating' => $feedback->rating,
                'reason' => $feedback->reason,
            ],
        ]);
    }

    public function escalate(
        Request $request,
        ChatMessage $message,
        WhatsAppAdminNotifier $notifier,
    ): JsonResponse {
        $validated = $request->validate([
            'session_key' => ['required', 'uuid'],
        ]);

        $message->loadMissing('conversation');

        abort_unless(
            $message->role === ChatMessage::ROLE_ASSISTANT
            && $message->status === 'insufficient_information'
            && hash_equals($message->conversation->session_key, $validated['session_key']),
            404,
        );

        $userMessage = ChatMessage::query()
            ->where('chat_conversation_id', $message->chat_conversation_id)
            ->where('role', ChatMessage::ROLE_USER)
            ->where('id', '<', $message->id)
            ->latest('id')
            ->firstOrFail();

        $escalation = UnansweredEscalation::query()->firstOrCreate(
            ['assistant_message_id' => $message->id],
            [
                'user_message_id' => $userMessage->id,
                'ticket_code' => 'SMLYR-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'status' => 'new',
                'whatsapp_status' => 'pending',
            ],
        );

        if ($escalation->wasRecentlyCreated) {
            $escalation->load('userMessage');
            $admins = User::query()
                ->whereIn('role', ['admin', 'superadmin'])
                ->where('status', 'Aktif')
                ->get();

            Notification::send($admins, new UnansweredQuestionEscalated($escalation));
            $notifier->send($escalation);
        }

        return response()->json([
            'data' => [
                'ticket_code' => $escalation->ticket_code,
                'status' => $escalation->status,
                'whatsapp_status' => $escalation->whatsapp_status,
            ],
        ], $escalation->wasRecentlyCreated ? 201 : 200);
    }

    private function resolveConversation(
        string $sessionKey,
        ?int $conversationId,
        string $question,
    ): ChatConversation {
        if ($conversationId !== null) {
            $conversation = ChatConversation::query()
                ->whereKey($conversationId)
                ->where('session_key', $sessionKey)
                ->first();

            if ($conversation !== null) {
                return $conversation;
            }
        }

        return ChatConversation::query()->create([
            'session_key' => $sessionKey,
            'title' => Str::limit(trim($question), 90),
            'last_message_at' => now(),
        ]);
    }

    private function existingConversation(
        string $sessionKey,
        ?int $conversationId,
    ): ?ChatConversation {
        if ($conversationId === null) {
            return null;
        }

        return ChatConversation::query()
            ->whereKey($conversationId)
            ->where('session_key', $sessionKey)
            ->first();
    }

    private function previousUserQuestion(?ChatConversation $conversation): ?string
    {
        if ($conversation === null) {
            return null;
        }

        return ChatMessage::query()
            ->where('chat_conversation_id', $conversation->id)
            ->where('role', ChatMessage::ROLE_USER)
            ->latest('id')
            ->value('content');
    }

    private function serializeMessage(ChatMessage $message): array
    {
        $message->loadMissing(['escalation', 'answeredEscalation']);

        return [
            'id' => $message->id,
            'role' => $message->role,
            'content' => $message->content,
            'status' => $message->status,
            'response_time_ms' => $message->response_time_ms,
            'created_at' => $message->created_at?->toIso8601String(),
            'sources' => $message->sources->map(fn ($source): array => [
                'document_id' => $source->document_id,
                'document_title' => $source->document_title,
                'section_title' => $source->section_title,
            ])->values(),
            'feedback' => $message->feedback === null ? null : [
                'rating' => $message->feedback->rating,
                'reason' => $message->feedback->reason,
            ],
            'escalation' => $message->escalation === null ? null : [
                'ticket_code' => $message->escalation->ticket_code,
                'status' => $message->escalation->status,
            ],
            'admin_answer' => $message->answeredEscalation === null ? null : [
                'ticket_code' => $message->answeredEscalation->ticket_code,
            ],
        ];
    }
}
