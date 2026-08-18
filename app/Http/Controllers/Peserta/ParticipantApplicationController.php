<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\Peserta\SaveParticipantApplicationRequest;
use App\Http\Requests\Peserta\UploadGuestbookProofRequest;
use App\Http\Requests\Peserta\UploadRequestLetterRequest;
use App\Http\Requests\Peserta\UploadWoppsDocumentRequest;
use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use App\Services\RequestLetterAutomatedChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ParticipantApplicationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Participant Application
    |--------------------------------------------------------------------------
    */

    public function store(SaveParticipantApplicationRequest $request): RedirectResponse
    {
        /** @var Participant $participant */
        $participant = $request->user('peserta');

        $application = $participant->applications()->latest()->first();

        if ($application instanceof ParticipantApplication) {
            abort_unless(
                $application->status === 'preparation',
                422,
                'Jenis layanan tidak dapat diubah setelah proses dimulai.'
            );

            $application->update($request->validated());
        } else {
            $participant->applications()->create([
                ...$request->validated(),
                'status' => 'preparation',
            ]);
        }

        return redirect()
            ->route('peserta.dashboard')
            ->with('status', 'Persiapan pengajuan Anda telah disimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | MAGANG / PKL
    |--------------------------------------------------------------------------
    */

    public function storeGuestbookProof(
        UploadGuestbookProofRequest $request
    ): RedirectResponse {
        $application = $this->magangApplication($request);

        $file = $request->file('guestbook_proof');

        $path = $file->store(
            "participant-applications/{$application->id}/guestbook"
        );

        abort_unless(
            is_string($path),
            500,
            'Bukti Buku Tamu gagal disimpan.'
        );

        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_GUESTBOOK,
            'version' => $application
                ->documents()
                ->where(
                    'type',
                    ParticipantApplicationDocument::TYPE_GUESTBOOK
                )
                ->max('version') + 1,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType()
                ?? 'application/octet-stream',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $application->update([
            'guestbook_confirmed_at' => now(),
            'status' => 'guestbook_submitted',
        ]);

        return back()->with(
            'status',
            'Bukti pengisian Buku Tamu berhasil disimpan.'
        );
    }

    public function storeRequestLetter(
        UploadRequestLetterRequest $request,
        RequestLetterAutomatedChecker $checker
    ): RedirectResponse {
        $application = $this->magangApplication($request);

        abort_unless(
            $application->guestbook_confirmed_at !== null,
            422,
            'Lengkapi bukti Buku Tamu terlebih dahulu.'
        );

        $currentLetter = $application
            ->documents()
            ->where(
                'type',
                ParticipantApplicationDocument::TYPE_REQUEST_LETTER
            )
            ->latest('version')
            ->first();

        abort_if(
            $currentLetter
                && $currentLetter->review_status
                    !== ParticipantApplicationDocument::REVIEW_REVISION
                && ! in_array(
                    $currentLetter->automated_check_status,
                    ['needs_revision', 'unreadable'],
                    true
                ),
            422,
            'Surat hanya dapat diunggah ulang ketika admin meminta revisi.'
        );

        $file = $request->file('request_letter');

        $path = $file->store(
            "participant-applications/{$application->id}/request-letters"
        );

        abort_unless(
            is_string($path),
            500,
            'Surat permohonan gagal disimpan.'
        );

        $document = $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
            'version' => ($currentLetter?->version ?? 0) + 1,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType()
                ?? 'application/pdf',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

$automatedResult = $checker->check($path, $request->user('peserta')?->name);        $document->update([
            'automated_check_status' => $automatedResult['status'],
            'automated_check_results' => $automatedResult,
            'automated_checked_at' => now(),
        ]);

        $needsCorrection = in_array(
            $automatedResult['status'],
            ['needs_revision', 'unreadable'],
            true
        );

        $application->update([
            'letter_submitted_at' => now(),
            'status' => $needsCorrection
                ? 'letter_revision_required'
                : 'letter_under_review',
        ]);

        return back()->with(
            'status',
            $needsCorrection
                ? 'Pemeriksaan awal selesai. Surat masih memerlukan perbaikan.'
                : 'Pemeriksaan awal selesai. Surat diteruskan untuk verifikasi admin.'
        );
    }

    public function confirmGoogleForm(
        Request $request
    ): RedirectResponse {
        $application = $this->magangApplication($request);

        abort_unless(
            $application->requestLetterApproved(),
            422,
            'Surat permohonan belum dinyatakan lolos.'
        );

        $application->update([
            'google_form_confirmed_at' => now(),
            'status' => 'response_pending',
        ]);

        return back()->with(
            'status',
            'Pengisian Google Form telah dikonfirmasi. Silakan menunggu surat balasan Dinas.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | WOPPS
    |--------------------------------------------------------------------------
    */

    public function storeWoppsDocument(
        UploadWoppsDocumentRequest $request
    ): RedirectResponse {
        $application = $this->woppsApplication($request);

        $type = $request->validated('type');
        $file = $request->file('document');

        $directory = match ($type) {
            ParticipantApplicationDocument::TYPE_WOPPS_IDENTITY =>
                'identity',

            ParticipantApplicationDocument::TYPE_WOPPS_REQUEST_LETTER =>
                'request-letter',

            ParticipantApplicationDocument::TYPE_WOPPS_PROPOSAL =>
                'proposal',

            ParticipantApplicationDocument::TYPE_WOPPS_ETHICS =>
                'ethics',

            default => abort(
                422,
                'Jenis dokumen WOPPS tidak valid.'
            ),
        };

        $current = $application
            ->documents()
            ->where('type', $type)
            ->latest('version')
            ->first();

        $path = $file->store(
            "participant-applications/{$application->id}/wopps/{$directory}"
        );

        abort_unless(
            is_string($path),
            500,
            'Dokumen WOPPS gagal disimpan.'
        );

        $application->documents()->create([
            'type' => $type,
            'version' => ($current?->version ?? 0) + 1,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType()
                ?? 'application/octet-stream',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
            'review_notes' => null,
            'reviewed_at' => null,
        ]);

        $application->update([
            'status' => 'wopps_documents_in_progress',
        ]);

        return back()->with(
            'status',
            'Dokumen WOPPS berhasil diunggah.'
        );
    }

    public function checkWoppsCompleteness(
        Request $request
    ): RedirectResponse {
        $application = $this->woppsApplication($request);

        $requiredTypes = [
            ParticipantApplicationDocument::TYPE_WOPPS_IDENTITY,
            ParticipantApplicationDocument::TYPE_WOPPS_REQUEST_LETTER,
            ParticipantApplicationDocument::TYPE_WOPPS_PROPOSAL,
        ];

        foreach ($requiredTypes as $type) {
            $document = $application->latestDocument($type);

            if (! $document) {
                $application->update([
                    'status' => 'wopps_revision_required',
                ]);

                return back()->with(
                    'status',
                    'Dokumen WOPPS belum lengkap. Silakan lengkapi dokumen yang masih diperlukan.'
                );
            }

            if (
                $document->review_status
                === ParticipantApplicationDocument::REVIEW_REVISION
            ) {
                $application->update([
                    'status' => 'wopps_revision_required',
                ]);

                return back()->with(
                    'status',
                    'Masih ada dokumen WOPPS yang perlu diperbaiki.'
                );
            }
        }

        $application->update([
            'status' => 'wopps_documents_complete',
        ]);

        return back()->with(
            'status',
            'Dokumen WOPPS sudah lengkap. Anda dapat melanjutkan ke Google Form resmi.'
        );
    }

    public function confirmWoppsGoogleForm(
        Request $request
    ): RedirectResponse {
        $application = $this->woppsApplication($request);

        abort_unless(
            $this->woppsDocumentsComplete($application),
            422,
            'Lengkapi dokumen WOPPS terlebih dahulu.'
        );

        $application->update([
            'google_form_confirmed_at' => now(),
            'status' => 'wopps_response_pending',
        ]);

        return redirect()->away(
            $application->googleFormUrl()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Download Document
    |--------------------------------------------------------------------------
    */

    public function downloadDocument(
        Request $request,
        ParticipantApplicationDocument $document
    ): StreamedResponse {
        $participant = $request->user('peserta');

        abort_unless(
            $document
                ->application()
                ->where('participant_id', $participant->id)
                ->exists(),
            403
        );

        abort_unless(
            Storage::disk('local')->exists($document->file_path),
            404
        );

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function magangApplication(
        Request $request
    ): ParticipantApplication {
        $application = $request
            ->user('peserta')
            ->applications()
            ->latest()
            ->firstOrFail();

        abort_unless(
            $application->service_type
                === ParticipantApplication::SERVICE_MAGANG_PKL,
            404
        );

        return $application;
    }

    private function woppsApplication(
        Request $request
    ): ParticipantApplication {
        $application = $request
            ->user('peserta')
            ->applications()
            ->with('documents')
            ->latest()
            ->firstOrFail();

        abort_unless(
            $application->service_type
                === ParticipantApplication::SERVICE_WOPPS,
            404
        );

        return $application;
    }

    private function woppsDocumentsComplete(
        ParticipantApplication $application
    ): bool {
        $requiredTypes = [
            ParticipantApplicationDocument::TYPE_WOPPS_IDENTITY,
            ParticipantApplicationDocument::TYPE_WOPPS_REQUEST_LETTER,
            ParticipantApplicationDocument::TYPE_WOPPS_PROPOSAL,
        ];

        foreach ($requiredTypes as $type) {
            $document = $application->latestDocument($type);

            if (! $document) {
                return false;
            }

            if (
                $document->review_status
                === ParticipantApplicationDocument::REVIEW_REVISION
            ) {
                return false;
            }
        }

        return true;
    }
}
