<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnansweredQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnansweredQuestionController extends Controller
{
    public function index(Request $request): View
    {
        $questions = UnansweredQuestion::query()
            ->when($request->search, fn ($q) => $q->where('question', 'like', "%{$request->search}%"))
            ->latest('last_asked')
            ->get();

        $metrics = [
            'total' => UnansweredQuestion::count(),
            'baru' => UnansweredQuestion::where('status', 'Baru')->count(),
            'ditinjau' => UnansweredQuestion::where('status', 'Ditinjau')->count(),
            'selesai' => UnansweredQuestion::where('status', 'Selesai')->count(),
        ];

        $selected = $request->selected
            ? UnansweredQuestion::find($request->selected)
            : null;

        return view('pages.admin.unanswered-questions', compact('questions', 'metrics', 'selected'));
    }

    public function markResolved(UnansweredQuestion $question): RedirectResponse
    {
        $question->update(['status' => 'Selesai']);

        return redirect()
            ->route('admin.unanswered-questions', ['selected' => $question->id])
            ->with('status', 'Pertanyaan ditandai selesai.');
    }
}