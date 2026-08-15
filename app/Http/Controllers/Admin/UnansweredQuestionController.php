<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\UnansweredEscalation;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class UnansweredQuestionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $items = UnansweredEscalation::query()
            ->with(['userMessage', 'assistantMessage.conversation', 'resolver'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('ticket_code', 'like', "%{$search}%")
                    ->orWhereHas('userMessage', fn ($message) => $message->where('content', 'like', "%{$search}%"));
            }))
            ->orderByRaw("case when status = 'resolved' then 1 else 0 end")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $metrics = [
            'open' => UnansweredEscalation::where('status', '!=', 'resolved')->count(),
            'resolved' => UnansweredEscalation::where('status', 'resolved')->count(),
            'raw_unanswered' => ChatMessage::where('role', 'assistant')->where('status', 'insufficient_information')->count(),
            'whatsapp_failed' => UnansweredEscalation::where('whatsapp_status', 'failed')->count(),
        ];

        return view('pages.admin.unanswered-questions', compact('items', 'metrics'));
    }

    public function show(UnansweredEscalation $escalation): View
    {
        $escalation->load([
            'userMessage',
            'assistantMessage.conversation.messages',
            'responseMessage',
            'resolver',
        ]);

        return view('pages.admin.unanswered-question-show', compact('escalation'));
    }

    public function respond(Request $request, UnansweredEscalation $escalation): RedirectResponse
    {
        $validated = $request->validate([
            'response' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'response.required' => 'Jawaban petugas wajib diisi.',
            'response.min' => 'Jawaban petugas minimal 10 karakter.',
        ]);

        DB::transaction(function () use ($escalation, $validated): void {
            $escalation->loadMissing(['assistantMessage.conversation', 'responseMessage']);

            $responseMessage = $escalation->responseMessage;

            if ($responseMessage === null) {
                $responseMessage = ChatMessage::query()->create([
                    'chat_conversation_id' => $escalation->assistantMessage->chat_conversation_id,
                    'role' => ChatMessage::ROLE_ASSISTANT,
                    'content' => $validated['response'],
                    'status' => 'admin_answer',
                    'response_time_ms' => null,
                ]);
            } else {
                $responseMessage->update(['content' => $validated['response']]);
            }

            $escalation->update([
                'admin_response' => $validated['response'],
                'response_message_id' => $responseMessage->id,
                'responded_at' => now(),
                'status' => 'resolved',
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            $escalation->assistantMessage->conversation->update(['last_message_at' => now()]);
        });

        return redirect()->route('admin.unanswered-questions.show', $escalation)
            ->with('status', 'Jawaban telah dikirim ke riwayat chatbot pengguna dan tiket diselesaikan.');
    }

    public function markResolved(UnansweredEscalation $escalation): RedirectResponse
    {
        $escalation->update([
            'status' => 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return back()->with('status', "Tiket {$escalation->ticket_code} ditandai selesai.");
    }
}
