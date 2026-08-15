<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\Peserta\SaveParticipantApplicationRequest;
use App\Http\Requests\Peserta\UploadGuestbookProofRequest;
use App\Http\Requests\Peserta\UploadRequestLetterRequest;
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
    public function store(SaveParticipantApplicationRequest $request): RedirectResponse
    {
        /** @var Participant $participant */
        $participant = $request->user('peserta');

        $application = $participant->applications()->latest()->first();

        if ($application instanceof ParticipantApplication) {
            abort_unless($application->status === 'preparation', 422, 'Jenis layanan tidak dapat diubah setelah proses dimulai.');
            $application->update($request->validated());
        } else {
            $participant->applications()->create([
                ...$request->validated(),
                'status' => 'preparation',
            ]);
        }

        return redirect()->route('peserta.dashboard')
            ->with('status', 'Persiapan pengajuan Anda telah disimpan.');
    }

    public function storeGuestbookProof(UploadGuestbookProofRequest $request): RedirectResponse
    {
        $application = $this->magangApplication($request);
        $file = $request->file('guestbook_proof');
        $path = $file->store("participant-applications/{$application->id}/guestbook");

        abort_unless(is_string($path), 500, 'Bukti Buku Tamu gagal disimpan.');

        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_GUESTBOOK,
            'version' => $application->documents()->where('type', ParticipantApplicationDocument::TYPE_GUESTBOOK)->max('version') + 1,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $application->update(['guestbook_confirmed_at' => now(), 'status' => 'guestbook_submitted']);

        return back()->with('status', 'Bukti pengisian Buku Tamu berhasil disimpan.');
    }

    public function storeRequestLetter(UploadRequestLetterRequest $request, RequestLetterAutomatedChecker $checker): RedirectResponse
    { 
        $application = $this->magangApplication($request);
        abort_unless($application->guestbook_confirmed_at !== null, 422, 'Lengkapi bukti Buku Tamu terlebih dahulu.');
        $currentLetter = $application->documents()->where('type', ParticipantApplicationDocument::TYPE_REQUEST_LETTER)->latest('version')->first();
        abort_if(
            $currentLetter
                && $currentLetter->review_status !== ParticipantApplicationDocument::REVIEW_REVISION
                && ! in_array($currentLetter->automated_check_status, ['needs_revision', 'unreadable'], true),
            422,
            'Surat hanya dapat diunggah ulang ketika admin meminta revisi.'
        );

        $file = $request->file('request_letter');
        $path = $file->store("participant-applications/{$application->id}/request-letters");

        abort_unless(is_string($path), 500, 'Surat permohonan gagal disimpan.');

        $document = $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
            'version' => ($currentLetter?->version ?? 0) + 1,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/pdf',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $automatedResult = $checker->check($path);
        $document->update([
            'review_status' => ParticipantApplicationDocument::REVIEW_APPROVED,
            'review_notes' => $request->input('review_notes'),
            'reviewed_at' => now(),
        ]);

        $needsCorrection = in_array($automatedResult['status'], ['needs_revision', 'unreadable'], true);
        $application->update([
            'letter_submitted_at' => now(),
            'status' => $needsCorrection ? 'letter_revision_required' : 'letter_under_review',
        ]);

        return back()->with('status', $needsCorrection
            ? 'Pemeriksaan awal selesai. Surat masih memerlukan perbaikan.'
            : 'Pemeriksaan awal selesai. Surat diteruskan untuk verifikasi admin.');
    }

    public function confirmGoogleForm(Request $request): RedirectResponse
    {
        $application = $this->magangApplication($request);
        abort_unless($application->requestLetterApproved(), 422, 'Surat permohonan belum dinyatakan lolos.');

        $application->update(['google_form_confirmed_at' => now(), 'status' => 'response_pending']);

        return back()->with('status', 'Pengisian Google Form telah dikonfirmasi. Silakan menunggu surat balasan Dinas.');
    }

    public function downloadDocument(Request $request, ParticipantApplicationDocument $document): StreamedResponse
    {
        $participant = $request->user('peserta');
        abort_unless($document->application()->where('participant_id', $participant->id)->exists(), 403);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    private function magangApplication(Request $request): ParticipantApplication
    {
        $application = $request->user('peserta')->applications()->latest()->firstOrFail();
        abort_unless($application->service_type === ParticipantApplication::SERVICE_MAGANG_PKL, 404);

        return $application;
    }
}
