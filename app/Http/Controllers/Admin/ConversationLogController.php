<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Support\KnowledgeBaseCategoryResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ConversationLogController extends Controller
{
    public function index(Request $request, KnowledgeBaseCategoryResolver $resolver): View
    {
        $query = $this->userMessageQuery($request);
        $total = (clone $query)->count();

        $messages = $query
            ->with(['conversation.messages.sources', 'conversation.messages.feedback'])
            ->orderByDesc('user_messages.created_at')
            ->paginate(10)
            ->withQueryString();

        $logs = $messages->through(function ($userMsg) use ($resolver) {
            $answer = $this->answerFor($userMsg);

            return (object) [
                'id' => $userMsg->id,
                'code' => 'C-'.str_pad((string) $userMsg->id, 3, '0', STR_PAD_LEFT),
                'question' => $userMsg->content,
                'answer' => $answer?->content ?? 'Chatbot tidak menemukan jawaban untuk pertanyaan ini.',
                'category' => $answer?->sources->first() ? $resolver->categoryFor($answer->sources->first()->document_id) : 'Umum',
                'status' => $this->displayStatus($answer),
                'sources' => $answer?->sources->count() ?? 0,
                'source_list' => $answer?->sources->map(fn ($s) => $s->document_title.' — '.$s->section_title)->all() ?? [],
                'response_time' => $answer?->response_time_ms ? round($answer->response_time_ms / 1000, 1) : 0,
                'feedback' => $answer?->feedback?->rating === 'positive' ? 'Positif' : ($answer?->feedback?->rating === 'negative' ? 'Negatif' : 'Belum Dinilai'),
                'feedback_reason' => $answer?->feedback?->reason,
                'created_at' => $userMsg->created_at,
                'formatted_created_at' => $this->formatForSurabaya($userMsg->created_at),
            ];
        });

        return view('pages.admin.conversation-logs', ['logs' => $logs, 'total' => $total]);
    }

    public function export(Request $request): Response
    {
        $messages = $this->userMessageQuery($request)
            ->with(['conversation.messages'])
            ->orderByDesc('user_messages.created_at')
            ->get();

        $csv = "\xEF\xBB\xBFPertanyaan,Status,Tanggal (WIB)\n";
        foreach ($messages as $m) {
            $answer = $this->answerFor($m);
            $csv .= implode(',', [
                '"'.str_replace('"', '""', $m->content).'"',
                $this->displayStatus($answer),
                '"'.$this->formatForSurabaya($m->created_at).' WIB"',
            ])."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="conversation-logs-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    private function userMessageQuery(Request $request): Builder
    {
        $query = ChatMessage::query()
            ->from('chat_messages as user_messages')
            ->select('user_messages.*')
            ->where('user_messages.role', ChatMessage::ROLE_USER)
            ->when(
                $request->filled('search'),
                fn (Builder $query) => $query->where('user_messages.content', 'like', '%'.$request->string('search').'%'),
            );

        $this->applyStatusFilter($query, $request->string('status')->toString());

        return $query;
    }

    private function applyStatusFilter(Builder $query, string $status): void
    {
        if ($status === '') {
            return;
        }

        if ($status === 'Tidak Ditemukan') {
            $query->whereNotExists(function (QueryBuilder $answerQuery): void {
                $this->nextAnswerQuery($answerQuery)
                    ->where('assistant_messages.status', 'success');
            });

            return;
        }

        $databaseStatus = match ($status) {
            'Dijawab' => 'success',
            'Error' => 'error',
            default => null,
        };

        if ($databaseStatus === null) {
            return;
        }

        $query->whereExists(function (QueryBuilder $answerQuery) use ($databaseStatus): void {
            $this->nextAnswerQuery($answerQuery)
                ->where('assistant_messages.status', $databaseStatus);
        });
    }

    private function nextAnswerQuery(QueryBuilder $query): QueryBuilder
    {
        return $query
            ->selectRaw('1')
            ->from('chat_messages as assistant_messages')
            ->whereColumn('assistant_messages.chat_conversation_id', 'user_messages.chat_conversation_id')
            ->where('assistant_messages.role', ChatMessage::ROLE_ASSISTANT)
            ->whereColumn('assistant_messages.id', '>', 'user_messages.id')
            ->whereNotExists(function (QueryBuilder $followingUserQuery): void {
                $followingUserQuery
                    ->selectRaw('1')
                    ->from('chat_messages as following_user_messages')
                    ->whereColumn('following_user_messages.chat_conversation_id', 'user_messages.chat_conversation_id')
                    ->where('following_user_messages.role', ChatMessage::ROLE_USER)
                    ->whereColumn('following_user_messages.id', '>', 'user_messages.id')
                    ->whereColumn('following_user_messages.id', '<', 'assistant_messages.id');
            });
    }

    private function answerFor(ChatMessage $userMessage): ?ChatMessage
    {
        $foundQuestion = false;

        foreach ($userMessage->conversation->messages as $message) {
            if (! $foundQuestion) {
                $foundQuestion = $message->id === $userMessage->id;

                continue;
            }

            if ($message->role === ChatMessage::ROLE_USER) {
                return null;
            }

            if ($message->role === ChatMessage::ROLE_ASSISTANT) {
                return $message;
            }
        }

        return null;
    }

    private function displayStatus(?ChatMessage $answer): string
    {
        return match ($answer?->status) {
            'success' => 'Dijawab',
            'error' => 'Error',
            default => 'Tidak Ditemukan',
        };
    }

    private function formatForSurabaya(Carbon $date): string
    {
        return $date->copy()->timezone('Asia/Jakarta')->format('Y-m-d H:i');
    }
}
