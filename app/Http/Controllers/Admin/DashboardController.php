<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatFeedback;
use App\Models\ChatMessage;
use App\Models\KnowledgeBaseDocument;
use App\Support\KnowledgeBaseCategoryResolver;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(KnowledgeBaseCategoryResolver $resolver): View
    {
        $totalConversations = ChatConversation::count();
        $totalQuestions = ChatMessage::where('role', 'user')->count();
        $today = ChatMessage::where('role', 'user')->whereDate('created_at', today())->count();

        $unanswered = ChatMessage::where('role', 'assistant')->where('status', 'insufficient_information')->count();

        $totalFeedback = ChatFeedback::count();
        $positiveFeedback = ChatFeedback::where('rating', 'positive')->count();
        $satisfaction = $totalFeedback > 0 ? round(($positiveFeedback / $totalFeedback) * 100) : 0;

        $avgResponseMs = ChatMessage::where('role', 'assistant')->whereNotNull('response_time_ms')->avg('response_time_ms');
        $avgResponseSeconds = $avgResponseMs ? round($avgResponseMs / 1000, 1) : 0;

        $metrics = [
            ['icon' => 'message-square', 'label' => 'Total Percakapan', 'value' => number_format($totalConversations), 'sub' => null, 'color' => 'ocean'],
            ['icon' => 'hash', 'label' => 'Total Pertanyaan', 'value' => number_format($totalQuestions), 'sub' => null, 'color' => 'teal'],
            ['icon' => 'activity', 'label' => 'Pertanyaan Hari Ini', 'value' => (string) $today, 'sub' => 'Diperbarui real-time', 'color' => 'indigo'],
            ['icon' => 'database', 'label' => 'Knowledge Base Aktif', 'value' => (string) KnowledgeBaseDocument::where('status', 'Ready')->count(), 'sub' => null, 'color' => 'amber'],
            ['icon' => 'inbox', 'label' => 'Pertanyaan Tidak Terjawab', 'value' => (string) $unanswered, 'sub' => null, 'color' => 'red'],
            ['icon' => 'thumbs-up', 'label' => 'Feedback Positif', 'value' => $satisfaction . '%', 'sub' => "Dari {$totalFeedback} feedback", 'color' => 'teal'],
            ['icon' => 'clock', 'label' => 'Rata-rata Response Time', 'value' => $avgResponseSeconds . 's', 'sub' => null, 'color' => 'ocean'],
        ];

        $trend = collect(range(29, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);
            return [
                'day' => $date->format('d'),
                'pertanyaan' => ChatMessage::where('role', 'user')->whereDate('created_at', $date)->count(),
                'dijawab' => ChatMessage::where('role', 'assistant')->where('status', 'success')->whereDate('created_at', $date)->count(),
            ];
        });

        $successCount = ChatMessage::where('role', 'assistant')->where('status', 'success')->count();
        $insufficientCount = ChatMessage::where('role', 'assistant')->where('status', 'insufficient_information')->count();
        $totalAnswered = $successCount + $insufficientCount;

        $statusData = [
            ['name' => 'Berhasil', 'value' => $totalAnswered > 0 ? round(($successCount / $totalAnswered) * 100) : 0, 'color' => '#0D9E8A'],
            ['name' => 'Tidak Ditemukan', 'value' => $totalAnswered > 0 ? round(($insufficientCount / $totalAnswered) * 100) : 0, 'color' => '#F59E0B'],
        ];

        $unansweredList = ChatMessage::where('role', 'assistant')
            ->where('status', 'insufficient_information')
            ->with('conversation')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($m) => [
                'question' => optional($m->conversation->messages->where('role', 'user')->last())->content ?? '-',
                'time' => $m->created_at->diffForHumans(),
            ]);

        $recentQuestions = ChatMessage::where('role', 'user')
            ->with(['conversation.messages' => fn ($q) => $q->where('role', 'assistant')])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($userMsg) use ($resolver) {
                $answer = $userMsg->conversation->messages->where('role', 'assistant')->first();

                return [
                    'question' => $userMsg->content,
                    'category' => $answer && $answer->sources->first()
                        ? $resolver->categoryFor($answer->sources->first()->document_id)
                        : 'Umum',
                    'status' => $answer?->status === 'success' ? 'Dijawab' : 'Tidak Ditemukan',
                    'time' => $userMsg->created_at->format('Y-m-d H:i'),
                ];
            });
            return view('pages.admin.dashboard', compact('metrics', 'trend', 'statusData', 'unansweredList', 'recentQuestions'
            ));
    }
}