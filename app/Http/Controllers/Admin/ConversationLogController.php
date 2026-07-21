<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConversationLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ConversationLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ConversationLog::query()
            ->when($request->search, fn ($q) => $q->where('question', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $total = ConversationLog::count();

        return view('pages.admin.conversation-logs', compact('logs', 'total'));
    }

    public function export(Request $request): Response
    {
        $logs = ConversationLog::query()
            ->when($request->search, fn ($q) => $q->where('question', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->get();

        $csv = "ID,Pertanyaan,Kategori,Status,Sumber,Score,Waktu,Tanggal\n";

        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->code,
                '"' . str_replace('"', '""', $log->question) . '"',
                $log->category,
                $log->status,
                $log->sources,
                $log->score,
                $log->response_time . 's',
                $log->created_at->format('Y-m-d H:i'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="conversation-logs-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}