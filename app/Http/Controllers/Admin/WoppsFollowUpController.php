<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class WoppsFollowUpController extends Controller
{
    public function index(): View
    {
        $applications = ParticipantApplication::query()
            ->where('service_type', ParticipantApplication::SERVICE_WOPPS)
            ->whereNotNull('google_form_confirmed_at')
            ->with(['participant', 'documents'])
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

        $application->update([
            'pic_contacted_at' => $application->pic_contacted_at ? null : now(),
        ]);

        return back()->with('success', $application->pic_contacted_at
            ? 'Peserta ditandai sudah dihubungi.'
            : 'Tanda sudah dihubungi dibatalkan.');
    }
}