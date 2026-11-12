<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use App\Notifications\ReplyLetterSent;
use App\Notifications\WoppsStatusUpdated;
use App\Services\ReplyLetterPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class WoppsFollowUpController extends Controller
{
    public function index(): View
    {
        $applications = ParticipantApplication::query()
            ->where('service_type', ParticipantApplication::SERVICE_WOPPS)
            ->whereNotNull('google_form_confirmed_at')
            ->with(['participant', 'replyLetter', 'documents'])
            ->latest('google_form_confirmed_at')
            ->paginate(10);

        return view('pages.admin.wopps-follow-up.index', compact('applications'));
    }

    public function download(ParticipantApplicationDocument $document)
    {
        abort_unless($document->type === ParticipantApplicationDocument::TYPE_WOPPS_FORM_PROOF, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function markContacted(ParticipantApplication $application): RedirectResponse
    {
        abort_unless($application->service_type === ParticipantApplication::SERVICE_WOPPS, 404);
        abort_unless($application->decision === 'accepted' && $application->completed_at === null, 422);

        $contacted = $application->pic_contacted_at === null;

        $application->update([
            'pic_contacted_at' => $contacted ? now() : null,
            'status' => $contacted ? 'wopps_contacted' : 'wopps_waiting_contact',
        ]);

        if ($contacted) {
            $application->participant->notify(new WoppsStatusUpdated($application->fresh(), 'contacted'));
        }

        return back()->with('success', $application->pic_contacted_at
            ? 'Peserta ditandai sudah dihubungi.'
            : 'Tanda sudah dihubungi dibatalkan.');
    }

    public function sendDecision(Request $request, ParticipantApplication $application): RedirectResponse
    {
        abort_unless($application->service_type === ParticipantApplication::SERVICE_WOPPS, 404);
        abort_unless($application->google_form_confirmed_at !== null, 422);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['accepted', 'rejected'])],
            'reply_letter' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'decision.required' => 'Keputusan WOPPS wajib dipilih.',
            'reply_letter.required' => 'Surat balasan wajib diunggah.',
            'reply_letter.mimes' => 'Surat balasan harus berupa PDF.',
        ]);

        $participant = $application->participant;
        $accepted = $validated['decision'] === 'accepted';
        $replyLetter = app(ReplyLetterPublisher::class)->publish(
            $application, $request->file('reply_letter'), [
                'decision' => $validated['decision'],
                'status' => $accepted ? 'wopps_waiting_contact' : 'rejected',
                'pic_contacted_at' => null, 'completed_at' => null,
                'official_started_at' => null, 'official_ended_at' => null,
            ],
        );

        $participant->notify(new ReplyLetterSent($replyLetter, $application->fresh()));

        return back()->with('success', 'Keputusan dan surat balasan WOPPS berhasil dikirim ke portal dan email peserta.');
    }

    public function markCompleted(ParticipantApplication $application): RedirectResponse
    {
        abort_unless($application->service_type === ParticipantApplication::SERVICE_WOPPS, 404);
        abort_unless($application->decision === 'accepted' && $application->pic_contacted_at !== null, 422);

        $application->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        $application->participant->notify(new WoppsStatusUpdated($application->fresh(), 'completed'));

        return back()->with('success', 'Layanan WOPPS ditandai selesai. Peserta sekarang dapat mengajukan layanan baru.');
    }
}
