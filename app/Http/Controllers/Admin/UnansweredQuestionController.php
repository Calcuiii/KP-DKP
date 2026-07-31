<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnansweredQuestionController extends Controller
{
    public function index(Request $request): View
    {
        $query = ChatMessage::where('role', 'assistant')
            ->where('status', 'insufficient_information')
            ->with('conversation.messages');

        $items = $query->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'question' => optional($m->conversation->messages->where('role', 'user')->where('created_at', '<=', $m->created_at)->last())->content ?? '-',
                'date' => $m->created_at,
            ])
            ->when($request->search, fn ($c) => $c->filter(fn ($i) => str_contains(strtolower($i['question']), strtolower($request->search))))
            ->groupBy('question')
            ->map(fn ($group, $question) => [
                'question' => $question,
                'frequency' => $group->count(),
                'first_asked' => $group->min('date'),
                'last_asked' => $group->max('date'),
            ])
            ->sortByDesc('frequency')
            ->values();

        $metrics = [
            'total' => $items->count(),
            'total_occurrences' => ChatMessage::where('role', 'assistant')->where('status', 'insufficient_information')->count(),
        ];

        return view('pages.admin.unanswered-questions', compact('items', 'metrics'));
    }
}