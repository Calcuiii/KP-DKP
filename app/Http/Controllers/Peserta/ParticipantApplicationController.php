<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\Peserta\SaveParticipantApplicationRequest;
use App\Http\Requests\Peserta\UploadEthicsApprovalRequest;
use App\Http\Requests\Peserta\UploadGuestbookProofRequest;
use App\Http\Requests\Peserta\UploadInternshipFormProofRequest;
use App\Http\Requests\Peserta\UploadRequestLetterRequest;
use App\Http\Requests\Peserta\UploadWoppsFormProofRequest;
use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use App\Services\RequestLetterAutomatedChecker;
use App\Services\EthicsApprovalAutomatedChecker;
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
        $application = $this->participantApplication($request);
        if ($application->service_type === ParticipantApplication::SERVICE_MAGANG_PKL) {
            abort_unless($application->guestbook_confirmed_at !== null, 422, 'Lengkapi bukti Buku Tamu terlebih dahulu.');
        }
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

        $automatedResult = $checker->check($path, $request->user('peserta')?->name, $application->service_type);
        $document->update([
            'automated_check_status' => $automatedResult['status'],
            'automated_check_results' => $automatedResult,
            'automated_checked_at' => now(),
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

    public function storeInternshipFormProof(UploadInternshipFormProofRequest $request): RedirectResponse
    {
        $application = $this->magangApplication($request);
        abort_unless($application->requestLetterApproved(), 422, 'Surat permohonan belum dinyatakan lolos.');
        abort_if(
            $application->documents()->where('type', ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF)->exists(),
            422,
            'Bukti pengisian Google Form sudah dikirim.'
        );

        $file = $request->file('internship_form_proof');
        $path = $file->store("participant-applications/{$application->id}/internship-form-proofs");
        abort_unless(is_string($path), 500, 'Bukti pengisian Google Form gagal disimpan.');

        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF,
            'version' => 1,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $application->update(['google_form_confirmed_at' => now(), 'status' => 'response_pending']);

        return back()->with('status', 'Bukti pengisian Google Form berhasil disimpan. Silakan menunggu surat balasan Dinas.');
    }

    public function storeEthicsApproval(UploadEthicsApprovalRequest $request, EthicsApprovalAutomatedChecker $checker): RedirectResponse
    {
        $application = $this->participantApplication($request);
        abort_unless($application->service_type === ParticipantApplication::SERVICE_WOPPS, 404);
        abort_unless($application->requestLetterApproved(), 422, 'Surat permohonan harus dinyatakan lolos terlebih dahulu.');

        $currentDocument = $application->documents()->where('type', ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL)->latest('version')->first();
        abort_if(
            $currentDocument
                && $currentDocument->review_status !== ParticipantApplicationDocument::REVIEW_REVISION
                && ! in_array($currentDocument->automated_check_status, ['needs_revision', 'unreadable'], true),
            422,
            'Dokumen hanya dapat diunggah ulang ketika admin meminta revisi.'
        );

        $file = $request->file('ethics_approval');
        $path = $file->store("participant-applications/{$application->id}/ethics-approvals");
        abort_unless(is_string($path), 500, 'Ethics Approval Statement Letter gagal disimpan.');

        $document = $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL,
            'version' => ($currentDocument?->version ?? 0) + 1,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/pdf',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $result = $checker->check($path, $request->user('peserta')?->name);
        $document->update([
            'automated_check_status' => $result['status'],
            'automated_check_results' => $result,
            'automated_checked_at' => now(),
        ]);

        $needsCorrection = in_array($result['status'], ['needs_revision', 'unreadable'], true);
        $application->update(['status' => $needsCorrection ? 'ethics_revision_required' : 'ethics_under_review']);

        return back()->with('status', $needsCorrection
            ? 'Pemeriksaan Ethics Approval selesai. Dokumen masih memerlukan perbaikan.'
            : 'Pemeriksaan Ethics Approval selesai. Dokumen diteruskan untuk verifikasi admin.');
    }

    public function storeWoppsFormProof(UploadWoppsFormProofRequest $request): RedirectResponse
    {
        $application = $this->participantApplication($request);
        abort_unless($application->service_type === ParticipantApplication::SERVICE_WOPPS, 404);
        abort_unless($application->ethicsApprovalApproved(), 422, 'Ethics Approval Statement Letter harus disetujui terlebih dahulu.');
        abort_if($application->google_form_confirmed_at !== null, 422, 'Bukti pengisian Form WOPPS sudah dikirim.');

        $file = $request->file('wopps_form_proof');
        $path = $file->store("participant-applications/{$application->id}/wopps-form-proofs");
        abort_unless(is_string($path), 500, 'Bukti pengisian Form WOPPS gagal disimpan.');

        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_WOPPS_FORM_PROOF,
            'version' => 1,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $application->update([
            'google_form_confirmed_at' => now(),
            'status' => 'wopps_form_submitted',
        ]);

        return back()->with('status', 'Bukti pengisian Form WOPPS berhasil disimpan. Silakan menunggu tindak lanjut Dinas.');
    }

    public function downloadDocument(Request $request, ParticipantApplicationDocument $document): StreamedResponse
    {
        $participant = $request->user('peserta');
        abort_unless($document->application()->where('participant_id', $participant->id)->exists(), 403);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function downloadResponseLetter(Request $request): StreamedResponse
    {
        $application = $this->magangApplication($request);
        abort_unless(filled($application->response_letter_path), 404);
        abort_unless(Storage::disk('local')->exists($application->response_letter_path), 404);

        return Storage::disk('local')->download(
            $application->response_letter_path,
            basename($application->response_letter_path)
        );
    }

    private function magangApplication(Request $request): ParticipantApplication
    {
        $application = $request->user('peserta')->applications()->latest()->firstOrFail();
        abort_unless($application->service_type === ParticipantApplication::SERVICE_MAGANG_PKL, 404);

        return $application;
    }

    private function participantApplication(Request $request): ParticipantApplication
    {
        return $request->user('peserta')->applications()->latest()->firstOrFail();
    }
}
