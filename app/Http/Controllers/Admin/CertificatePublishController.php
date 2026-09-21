<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ParticipantApplication;
use App\Notifications\CertificateIssued;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CertificatePublishController extends Controller
{
    public function index(): View
    {
        $applications = ParticipantApplication::query()
            ->whereNotNull('presentation_submitted_at')
            ->whereNotNull('completion_form_submitted_at')
            ->with('participant')
            ->orderByRaw('certificate_published_at is not null')
            ->latest('completion_form_submitted_at')
            ->paginate(10);

        return view('pages.admin.sertifikat.index', compact('applications'));
    }

    public function previewPresentation(ParticipantApplication $application)
    {
        abort_unless(
            $application->presentation_photo_path
                && Storage::disk('local')->exists($application->presentation_photo_path),
            404
        );

        return response()->file(
            Storage::disk('local')->path($application->presentation_photo_path)
        );
    }

    public function previewCompletionProof(ParticipantApplication $application)
    {
        abort_unless(
            $application->completion_form_proof_path
                && Storage::disk('local')->exists($application->completion_form_proof_path),
            404
        );

        return response()->file(
            Storage::disk('local')->path($application->completion_form_proof_path)
        );
    }

    public function publish(Request $request, ParticipantApplication $application): RedirectResponse
    {
        abort_unless(
            $application->presentation_submitted_at
                && $application->completion_form_submitted_at,
            422
        );

        $validated = $request->validate([
            'spreadsheet_verified' => ['accepted'],
            'certificate' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'spreadsheet_verified.accepted' =>
                'Konfirmasi verifikasi data pada spreadsheet wajib dicentang sebelum sertifikat dikirim.',
            'certificate.required' => 'File sertifikat wajib diunggah.',
            'certificate.mimes' => 'Sertifikat harus berupa PDF.',
        ]);

        if ($application->certificate_path
            && Storage::disk('local')->exists($application->certificate_path)) {
            Storage::disk('local')->delete($application->certificate_path);
        }

        $path = $request->file('certificate')->store('certificates', 'local');

        $application->update([
            'certificate_path' => $path,
            'certificate_published_at' => now(),
            'certificate_emailed_at' => now(),
            'completion_checklist' => array_merge(
                $application->completion_checklist ?? [],
                [
                    'spreadsheet_verified' => true,
                    'spreadsheet_verified_at' => now()->toDateTimeString(),
                    'spreadsheet_verified_by' => auth()->id(),
                ]
            ),
        ]);

        $application->participant->notify(
            new CertificateIssued($application->fresh())
        );

        ActivityLog::record(
            'Kirim Sertifikat',
            'Sertifikat',
            "Mengirim sertifikat untuk \"{$application->participant->name}\""
        );

        return back()->with(
            'success',
            'Sertifikat berhasil dikirim ke portal dan email peserta.'
        );
    }
}