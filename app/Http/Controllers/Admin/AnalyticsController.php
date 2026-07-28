<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\KnowledgeBaseDocument;
use App\Support\KnowledgeBaseCategoryResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request, KnowledgeBaseCategoryResolver $resolver): View
    {
        $days = (int) ($request->period ?? 30);
        $from = Carbon::now()->subDays($days);

        $totalConversations = ChatConversation::where('created_at', '>=', $from)->count();
        $totalQuestions = ChatMessage::where('role', 'user')->where('created_at', '>=', $from)->count();
        $answered = ChatMessage::where('role', 'assistant')->where('status', 'success')->where('created_at', '>=', $from)->count();
        $totalAnswers = ChatMessage::where('role', 'assistant')->where('created_at', '>=', $from)->count();
        $answerRate = $totalAnswers > 0 ? round(($answered / $totalAnswers) * 100) : 0;

        $questionTrend = ChatMessage::where('role', 'user')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $categoryData = ChatMessage::where('role', 'assistant')
            ->where('created_at', '>=', $from)
            ->with('sources')
            ->get()
            ->flatMap(fn ($m) => $m->sources->take(1))
            ->groupBy(fn ($s) => $resolver->categoryFor($s->document_id))
            ->map(fn ($group, $name) => ['name' => $name, 'value' => $group->count()])
            ->sortByDesc('value')
            ->values();

        $totalChunks = KnowledgeBaseDocument::sum('chunks_count');
        $kbUsage = KnowledgeBaseDocument::where('chunks_count', '>', 0)
            ->orderByDesc('chunks_count')
            ->limit(5)
            ->get()
            ->map(fn ($doc) => [
                'name' => $doc->name,
                'percentage' => $totalChunks > 0 ? round(($doc->chunks_count / $totalChunks) * 100) : 0,
            ]);

        return view('pages.admin.analytics', compact(
            'days', 'totalConversations', 'totalQuestions', 'answerRate', 'questionTrend', 'categoryData', 'kbUsage'
        ));
    }
}