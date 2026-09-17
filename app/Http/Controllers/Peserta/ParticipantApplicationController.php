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
use App\Models\User;
use App\Notifications\DocumentAutomatedCheckPassed;
use App\Notifications\InternshipFormSubmitted;
use App\Notifications\WoppsFormSubmitted;
use App\Services\EthicsApprovalAutomatedChecker;
use App\Services\RequestLetterAutomatedChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ParticipantApplicationController extends Controller
{
    public function store(SaveParticipantApplicationRequest $request): RedirectResponse
    {
        /** @var Participant $participant */
        $participant = $request->user('peserta');

        DB::transaction(function () use ($participant, $request) {
            Participant::query()
                ->whereKey($participant->id)
                ->lockForUpdate()
                ->firstOrFail();

            $application = $participant->applications()->latest()->first();

            if ($application instanceof ParticipantApplication) {
                if ($application->isClosed()) {
                    $participant->applications()->create([
                        ...$request->validated(),
                        'status' => 'preparation',
                    ]);
                } else {
                    abort_unless(
                        $application->status === 'preparation',
                        422,
                        'Anda masih memiliki satu pengajuan aktif. Selesaikan pengajuan tersebut sebelum membuat pengajuan baru.'
                    );

                    $application->update($request->validated());
                }
            } else {
                $participant->applications()->create([
                    ...$request->validated(),
                    'status' => 'preparation',
                ]);
            }
        });

        return redirect()
            ->route('peserta.dashboard')
            ->with('status', 'Persiapan pengajuan Anda telah disimpan.');
    }

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
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
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
        $application = $this->participantApplication($request);

        if (
            $application->service_type
            === ParticipantApplication::SERVICE_MAGANG_PKL
        ) {
            abort_unless(
                $application->guestbook_confirmed_at !== null,
                422,
                'Lengkapi bukti Buku Tamu terlebih dahulu.'
            );
        }

        $currentLetter = $application
            ->documents()
            ->where(
                'type',
                ParticipantApplicationDocument::TYPE_REQUEST_LETTER
            )
            ->latest('version')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Cek apakah peserta memang diperbolehkan upload ulang
        |--------------------------------------------------------------------------
        */

        $canResubmitAfterNoCertificateDecision =
            $currentLetter
            && $currentLetter->review_status
                === ParticipantApplicationDocument::REVIEW_APPROVED
            && $currentLetter->certificate_eligible === false
            && $application->certificate_follow_up_choice === 'upload_again';

        /*
        |--------------------------------------------------------------------------
        | Cegah upload ulang sembarangan
        |--------------------------------------------------------------------------
        */

        abort_if(
            $currentLetter
            && ! $canResubmitAfterNoCertificateDecision
            && $currentLetter->review_status
                !== ParticipantApplicationDocument::REVIEW_REVISION
            && ! in_array(
                $currentLetter->automated_check_status,
                ['needs_revision', 'unreadable'],
                true
            ),
            422,
            'Surat hanya dapat diunggah ulang ketika admin meminta revisi atau Anda memilih untuk memperbaiki surat terkait sertifikat.'
        );

        /*
        |--------------------------------------------------------------------------
        | Cek surat yang sama
        |--------------------------------------------------------------------------
        */

        if ($request->filled('letter_institution')) {
            $fileHash = hash_file(
                'sha256',
                $request->file('request_letter')->getRealPath()
            );

            $alreadyApplied = ParticipantApplicationDocument::query()
                ->where(
                    'type',
                    ParticipantApplicationDocument::TYPE_REQUEST_LETTER
                )
                ->where(
                    'letter_group_key',
                    ParticipantApplicationDocument::letterGroupKey(
                        $request->validated('letter_institution'),
                        $fileHash
                    )
                )
                ->where(
                    'participant_application_id',
                    '!=',
                    $application->id
                )
                ->whereHas(
                    'application',
                    fn ($query) => $query
                        ->where(
                            'participant_id',
                            $application->participant_id
                        )
                        ->where(
                            'service_type',
                            $application->service_type
                        )
                )
                ->exists();

            if ($alreadyApplied) {
                throw ValidationException::withMessages([
                    'request_letter' =>
                        'Anda sudah memiliki pengajuan dengan surat ini. Gunakan pengajuan sebelumnya atau surat baru untuk kesempatan kegiatan yang berbeda.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan file baru
        |--------------------------------------------------------------------------
        */

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
            'mime_type' => $file->getMimeType() ?? 'application/pdf',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,

            /*
            |--------------------------------------------------------------------------
            | Surat baru belum diketahui status sertifikatnya.
            |--------------------------------------------------------------------------
            */
            'certificate_eligible' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pemeriksaan otomatis
        |--------------------------------------------------------------------------
        */

        $automatedResult = $checker->check(
            $path,
            $request->user('peserta')?->name,
            $application->service_type
        );

        /*
        |--------------------------------------------------------------------------
        | Data surat bersama
        |--------------------------------------------------------------------------
        */

        if ($request->filled('letter_institution')) {
            $fileHash = hash_file(
                'sha256',
                $request->file('request_letter')->getRealPath()
            );

            $document->forceFill([
                'letter_institution' => trim(
                    $request->validated('letter_institution')
                ),
                'letter_group_key' =>
                    ParticipantApplicationDocument::letterGroupKey(
                        $request->validated('letter_institution'),
                        $fileHash
                    ),
            ])->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan hasil pemeriksaan otomatis
        |--------------------------------------------------------------------------
        */

        $document->update([
            'automated_check_status' => $automatedResult['status'],
            'automated_check_results' => $automatedResult,
            'automated_checked_at' => now(),
        ]);

        $needsCorrection = in_array(
            $automatedResult['status'],
            ['needs_revision', 'unreadable'],
            true
        );

        /*
        |--------------------------------------------------------------------------
        | Reset pilihan sertifikat
        |
        | Ini penting ketika peserta upload surat baru.
        |--------------------------------------------------------------------------
        */

        $application->update([
            'letter_submitted_at' => now(),
            'certificate_follow_up_choice' => null,
            'certificate_follow_up_at' => null,
            'status' => $needsCorrection
                ? 'letter_revision_required'
                : 'letter_under_review',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Beritahu admin jika pemeriksaan otomatis lolos
        |--------------------------------------------------------------------------
        */

        if (! $needsCorrection) {
            User::query()
                ->where('role', 'superadmin')
                ->where('status', 'Aktif')
                ->get()
                ->each(
                    fn (User $admin) =>
                    $admin->notify(
                        new DocumentAutomatedCheckPassed($document)
                    )
                );
        }

        return back()->with(
            'status',
            $needsCorrection
                ? 'Pemeriksaan awal selesai. Surat masih memerlukan perbaikan.'
                : 'Pemeriksaan awal selesai. Surat diteruskan untuk verifikasi admin.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PILIH TINDAK LANJUT SERTIFIKAT
    |--------------------------------------------------------------------------
    */

    public function chooseCertificateFollowUp(
        Request $request
    ): RedirectResponse {
        $application = $this->magangApplication($request);

        $letter = $application->latestDocument(
            ParticipantApplicationDocument::TYPE_REQUEST_LETTER
        );

        abort_unless(
            $letter?->review_status
                === ParticipantApplicationDocument::REVIEW_APPROVED
                && $letter->certificate_eligible === false,
            422,
            'Pilihan tindak lanjut sertifikat belum tersedia.'
        );

        $validated = $request->validate([
            'certificate_follow_up_choice' => [
                'required',
                'in:upload_again,continue_without_upload',
            ],
        ]);

        $choice = $validated['certificate_follow_up_choice'];

        $application->update([
            'certificate_follow_up_choice' => $choice,
            'certificate_follow_up_at' => now(),
            'status' => $choice === 'upload_again'
                ? 'letter_resubmission_required'
                : 'letter_approved_no_certificate',
        ]);

        return back()->with(
            'status',
            $choice === 'upload_again'
                ? 'Pilihan disimpan. Silakan unggah ulang surat permohonan yang mencantumkan permintaan penerbitan sertifikat.'
                : 'Pilihan disimpan. Anda dapat melanjutkan ke tahap pengisian Google Form tanpa mengunggah ulang surat.'
        );
    }

    public function storeInternshipFormProof(
        UploadInternshipFormProofRequest $request
    ): RedirectResponse {
        $application = $this->magangApplication($request);

        /*
        |--------------------------------------------------------------------------
        | Google Form hanya boleh diakses jika memang sudah terbuka
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $application->canProceedToInternshipForm(),
            422,
            'Tahap pengisian Google Form belum dapat diakses. Silakan selesaikan keputusan terkait surat permohonan terlebih dahulu.'
        );

        abort_if(
            $application
                ->documents()
                ->where(
                    'type',
                    ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF
                )
                ->exists(),
            422,
            'Bukti pengisian Google Form sudah dikirim.'
        );

        $file = $request->file('internship_form_proof');

        $path = $file->store(
            "participant-applications/{$application->id}/internship-form-proofs"
        );

        abort_unless(
            is_string($path),
            500,
            'Bukti pengisian Google Form gagal disimpan.'
        );

        $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF,
            'version' => 1,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $application->update([
            'google_form_confirmed_at' => now(),
            'status' => 'response_pending',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Beritahu admin
        |--------------------------------------------------------------------------
        */

        User::query()
            ->where('role', 'superadmin')
            ->where('status', 'Aktif')
            ->get()
            ->each(
                fn (User $admin) =>
                $admin->notify(
                    new InternshipFormSubmitted($application)
                )
            );

        return back()->with(
            'status',
            'Bukti pengisian Google Form berhasil disimpan. Silakan menunggu surat balasan Dinas.'
        );
    }

    public function storeEthicsApproval(
        UploadEthicsApprovalRequest $request,
        EthicsApprovalAutomatedChecker $checker
    ): RedirectResponse {
        $application = $this->participantApplication($request);

        abort_unless(
            $application->service_type
                === ParticipantApplication::SERVICE_WOPPS,
            404
        );

        abort_unless(
            $application->requestLetterApproved(),
            422,
            'Surat permohonan harus dinyatakan lolos terlebih dahulu.'
        );

        $currentDocument = $application
            ->documents()
            ->where(
                'type',
                ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL
            )
            ->latest('version')
            ->first();

        abort_if(
            $currentDocument
            && $currentDocument->review_status
                !== ParticipantApplicationDocument::REVIEW_REVISION
            && ! in_array(
                $currentDocument->automated_check_status,
                ['needs_revision', 'unreadable'],
                true
            ),
            422,
            'Dokumen hanya dapat diunggah ulang ketika admin meminta revisi.'
        );

        $file = $request->file('ethics_approval');

        $path = $file->store(
            "participant-applications/{$application->id}/ethics-approvals"
        );

        abort_unless(
            is_string($path),
            500,
            'Ethics Approval Statement Letter gagal disimpan.'
        );

        $document = $application->documents()->create([
            'type' => ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL,
            'version' => ($currentDocument?->version ?? 0) + 1,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/pdf',
            'file_size' => $file->getSize(),
            'review_status' => ParticipantApplicationDocument::REVIEW_SUBMITTED,
        ]);

        $result = $checker->check(
            $path,
            $request->user('peserta')?->name
        );

        $document->update([
            'automated_check_status' => $result['status'],
            'automated_check_results' => $result,
            'automated_checked_at' => now(),
        ]);

        $needsCorrection = in_array(
            $result['status'],
            ['needs_revision', 'unreadable'],
            true
        );

        $application->update([
            'status' => $needsCorrection
                ? 'ethics_revision_required'
                : 'ethics_under_review',
        ]);

        if (! $needsCorrection) {
            User::query()
                ->where('role', 'superadmin')
                ->where('status', 'Aktif')
                ->get()
                ->each(
                    fn (User $admin) =>
                    $admin->notify(
                        new DocumentAutomatedCheckPassed($document)
                    )
                );
        }

        return back()->with(
            'status',
            $needsCorrection
                ? 'Pemeriksaan Ethics Approval selesai. Dokumen masih memerlukan perbaikan.'
                : 'Pemeriksaan Ethics Approval selesai. Dokumen diteruskan untuk verifikasi admin.'
        );
    }

    public function storeWoppsFormProof(
        UploadWoppsFormProofRequest $request
    ): RedirectResponse {
        $application = $this->participantApplication($request);

        abort_unless(
            $application->service_type
                === ParticipantApplication::SERVICE_WOPPS,
            404
        );

        abort_unless(
            $application->ethicsApprovalApproved(),
            422,
            'Ethics Approval Statement Letter harus disetujui terlebih dahulu.'
        );

        abort_if(
            $application->google_form_confirmed_at !== null,
            422,
            'Bukti pengisian Form WOPPS sudah dikirim.'
        );

        $file = $request->file('wopps_form_proof');

        $path = $file->store(
            "participant-applications/{$application->id}/wopps-form-proofs"
        );

        abort_unless(
            is_string($path),
            500,
            'Bukti pengisian Form WOPPS gagal disimpan.'
        );

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

        User::query()
            ->where('role', 'superadmin')
            ->where('status', 'Aktif')
            ->get()
            ->each(
                fn (User $admin) =>
                $admin->notify(
                    new WoppsFormSubmitted($application)
                )
            );

        return back()->with(
            'status',
            'Bukti pengisian Form WOPPS berhasil disimpan. Silakan menunggu tindak lanjut Dinas.'
        );
    }

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

    public function viewDocument(
        Request $request,
        ParticipantApplicationDocument $document
    ) {
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

        $path = Storage::disk('local')->path($document->file_path);

        return response()->file($path, [
            'Content-Type' => $document->mime_type ?? 'application/pdf',
            'Content-Disposition' =>
                'inline; filename="' .
                addslashes(
                    $document->original_name
                    ?? basename($document->file_path)
                ) .
                '"',
        ]);
    }

    public function downloadResponseLetter(
        Request $request
    ): StreamedResponse {
        /** @var Participant $participant */
        $participant = $request->user('peserta');

        $application = $participant
            ->applications()
            ->latest()
            ->firstOrFail();

        $replyLetter = $application->replyLetter;

        abort_unless(
            $replyLetter && filled($replyLetter->file_path),
            404
        );

        abort_unless(
            Storage::disk('public')->exists($replyLetter->file_path),
            404
        );

        return Storage::disk('public')->download(
            $replyLetter->file_path,
            $replyLetter->original_name
                ?? basename($replyLetter->file_path)
        );
    }

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

    private function participantApplication(
        Request $request
    ): ParticipantApplication {
        return $request
            ->user('peserta')
            ->applications()
            ->latest()
            ->firstOrFail();
    }
}