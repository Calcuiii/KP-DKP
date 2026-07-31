<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Support\KnowledgeBaseCategoryResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ConversationLogController extends Controller
{
    public function index(Request $request, KnowledgeBaseCategoryResolver $resolver): View
    {
        $messages = ChatMessage::where('role', 'user')
            ->with(['conversation.messages.sources', 'conversation.messages.feedback'])
            ->when($request->search, fn ($q) => $q->where('content', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $logs = $messages->through(function ($userMsg) use ($resolver) {
    $answer = $userMsg->conversation->messages->where('role', 'assistant')->first();

    return (object) [
        'id' => $userMsg->id,
        'code' => 'C-' . str_pad((string) $userMsg->id, 3, '0', STR_PAD_LEFT),
        'question' => $userMsg->content,
        'answer' => $answer?->content ?? 'Chatbot tidak menemukan jawaban untuk pertanyaan ini.',
        'category' => $answer?->sources->first() ? $resolver->categoryFor($answer->sources->first()->document_id) : 'Umum',
        'status' => $answer?->status === 'success' ? 'Dijawab' : 'Tidak Ditemukan',
        'sources' => $answer?->sources->count() ?? 0,
        'source_list' => $answer?->sources->map(fn ($s) => $s->document_title . ' — ' . $s->section_title)->all() ?? [],
        'response_time' => $answer?->response_time_ms ? round($answer->response_time_ms / 1000, 1) : 0,
        'feedback' => $answer?->feedback?->rating === 'positive' ? 'Positif' : ($answer?->feedback?->rating === 'negative' ? 'Negatif' : 'Belum Dinilai'),
        'feedback_reason' => $answer?->feedback?->reason,
        'created_at' => $userMsg->created_at,
    ];
});

        $total = ChatMessage::where('role', 'user')->count();

        return view('pages.admin.conversation-logs', ['logs' => $logs, 'total' => $total]);
    }

    public function export(Request $request): Response
    {
        $messages = ChatMessage::where('role', 'user')
            ->with(['conversation.messages'])
            ->when($request->search, fn ($q) => $q->where('content', 'like', "%{$request->search}%"))
            ->latest()
            ->get();

        $csv = "Pertanyaan,Status,Tanggal\n";
        foreach ($messages as $m) {
            $answer = $m->conversation->messages->where('role', 'assistant')->first();
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $m->content) . '"',
                $answer?->status === 'success' ? 'Dijawab' : 'Tidak Ditemukan',
                $m->created_at->format('Y-m-d H:i'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="conversation-logs-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}