<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ParticipantApplication;
use App\Notifications\CertificateIssued;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CertificatePublishController extends Controller
{
    public function index(): View
    {
        $applications = ParticipantApplication::query()
            ->where('service_type', ParticipantApplication::SERVICE_MAGANG_PKL)
            ->whereIn('decision', ['accepted', 'approved', 'diterima'])
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
            $application->service_type === ParticipantApplication::SERVICE_MAGANG_PKL
                && in_array($application->decision, ['accepted', 'approved', 'diterima'], true)
                && $application->presentation_submitted_at
                && $application->completion_form_submitted_at,
            422
        );

        $validated = $request->validate([
            'spreadsheet_verified' => ['accepted'],
            'certificate' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'spreadsheet_verified.accepted' => 'Konfirmasi verifikasi data pada spreadsheet wajib dicentang sebelum sertifikat dikirim.',
            'certificate.required' => 'File sertifikat wajib diunggah.',
            'certificate.mimes' => 'Sertifikat harus berupa PDF.',
        ]);

        $path = $request->file('certificate')->store('certificates', 'local');
        abort_unless(is_string($path), 500, 'Sertifikat gagal disimpan.');
        $oldPath = null;
        try {
            DB::transaction(function () use ($application, $path, &$oldPath): void {
                $locked = ParticipantApplication::query()->lockForUpdate()->findOrFail($application->id);
                abort_unless($locked->service_type === ParticipantApplication::SERVICE_MAGANG_PKL
                    && in_array($locked->decision, ['accepted', 'approved', 'diterima'], true)
                    && $locked->presentation_submitted_at && $locked->completion_form_submitted_at, 422);
                $oldPath = $locked->certificate_path;
                $locked->update([
                    'certificate_path' => $path,
                    'certificate_published_at' => now(),
                    'certificate_emailed_at' => null,
                    'completion_checklist' => array_merge(
                        $locked->completion_checklist ?? [],
                        [
                            'spreadsheet_verified' => true,
                            'spreadsheet_verified_at' => now()->toDateTimeString(),
                            'spreadsheet_verified_by' => auth()->id(),
                        ]
                    ),
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }
        $application->refresh();
        $application->participant->notifyNow(new CertificateIssued($application), ['database']);
        try {
            $application->participant->notifyNow(new CertificateIssued($application), ['mail']);
            $application->newQuery()->whereKey($application->id)->where('certificate_path', $path)
                ->update(['certificate_emailed_at' => now()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Sertifikat tersedia di portal, tetapi email belum berhasil dikirim. Silakan ulangi pengiriman.');
        }

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
