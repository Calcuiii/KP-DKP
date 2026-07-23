<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConversationLog;
use App\Models\KnowledgeBaseDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $days = (int) ($request->period ?? 30);
        $from = Carbon::now()->subDays($days);

        $totalConversations = ConversationLog::where('created_at', '>=', $from)->count();
        $totalQuestions = ConversationLog::where('created_at', '>=', $from)->count();
        $answered = ConversationLog::where('created_at', '>=', $from)->where('status', 'Dijawab')->count();
        $answerRate = $totalQuestions > 0 ? round(($answered / $totalQuestions) * 100) : 0;

        // Tren pertanyaan harian
        $questionTrend = ConversationLog::where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();


        // Kategori pertanyaan terbanyak
        $categoryData = ConversationLog::where('created_at', '>=', $from)
            ->selectRaw('category as name, COUNT(*) as value')
            ->groupBy('category')
            ->orderByDesc('value')
            ->get();

        // Penggunaan Knowledge Base (top sumber, berdasarkan chunks_count)
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
            'days', 'totalConversations', 'totalQuestions', 'answerRate',  'questionTrend', 'categoryData', 'kbUsage'
        ));
    }

    public function export(Request $request)
    {
        $days = (int) ($request->period ?? 30);
        $from = Carbon::now()->subDays($days);

        $logs = ConversationLog::where('created_at', '>=', $from)->get();

        $csv = "Tanggal,Kategori,Status,Score\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->created_at->format('Y-m-d H:i'),
                $log->category,
                $log->status,
                $log->score,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics-report-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}