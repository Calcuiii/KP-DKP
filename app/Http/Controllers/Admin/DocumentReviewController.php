<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use App\Notifications\DocumentReviewUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentReviewController extends Controller
{
    /**
     * Daftar dokumen yang perlu diperiksa admin.
     */
    public function index(Request $request): View
    {
        $serviceType = $request->query('service_type') === ParticipantApplication::SERVICE_WOPPS
            ? ParticipantApplication::SERVICE_WOPPS
            : ParticipantApplication::SERVICE_MAGANG_PKL;
        $documents = ParticipantApplicationDocument::query()
            ->whereIn('type', $this->reviewableTypes())
            ->whereHas('application', fn ($query) => $query->where('service_type', $serviceType))
            ->with([
                'application.participant',
            ])
            ->latest()
            ->paginate(10)
            ->appends(['service_type' => $serviceType]);

        return view(
            'pages.admin.pemeriksaan-dokumen.index',
            compact('documents', 'serviceType')
        );
    }

    /**
     * Menampilkan detail dokumen yang akan diperiksa.
     */
    public function show(
        ParticipantApplicationDocument $document
    ): View {
        abort_unless(
            in_array(
                $document->type,
                $this->reviewableTypes(),
                true
            ),
            404
        );

        $document->load([
            'application.participant',
        ]);

        $relatedLetters = ParticipantApplicationDocument::query()
            ->where(
                'type',
                ParticipantApplicationDocument::TYPE_REQUEST_LETTER
            )
            ->where(
                'letter_group_key',
                $document->letter_group_key
            )
            ->whereNotNull('letter_group_key')
            ->whereHas(
                'application',
                fn ($query) => $query->where(
                    'service_type',
                    $document->application->service_type
                )
            )
            ->whereRaw(
                'version = (
                    select max(latest.version)
                    from participant_application_documents as latest
                    where latest.participant_application_id =
                        participant_application_documents.participant_application_id
                    and latest.type =
                        participant_application_documents.type
                )'
            )
            ->with('application.participant')
            ->get();

        return view(
            'pages.admin.pemeriksaan-dokumen.show',
            compact(
                'document',
                'relatedLetters'
            )
        );
    }

    /** Menyetujui dokumen dan identitas peserta tanpa pemeriksaan permintaan sertifikat. */
    public function approve(
        Request $request,
        ParticipantApplicationDocument $document
    ): RedirectResponse {
        /*
         * Pastikan dokumen memang merupakan dokumen
         * yang boleh diperiksa oleh admin.
         */
        abort_unless(
            in_array(
                $document->type,
                $this->reviewableTypes(),
                true
            ),
            404
        );

        abort_if($document->application->completed_at !== null || $document->application->decision !== null, 422);
        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:2000'],
            'participant_identity_confirmed' => $document->letter_group_key
                ? ['accepted'] : ['sometimes', 'accepted'],
        ]);
        $document->update([
            'review_status' => ParticipantApplicationDocument::REVIEW_APPROVED,
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_at' => now(), 'certificate_eligible' => null,
        ]);
        $document->application()->update([
            'status' => $document->type === ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL
                ? 'ethics_approved' : 'letter_approved',
            'certificate_follow_up_choice' => null, 'certificate_follow_up_at' => null,
        ]);
        $this->notifyParticipant(
            $document,
            ParticipantApplicationDocument::REVIEW_APPROVED
        );

        /*
         * Kembali ke halaman pemeriksaan dokumen.
         */
        return redirect()
            ->route(
                'admin.pemeriksaan-dokumen.show',
                $document
            )
            ->with(
                'success',
                'Surat berhasil disetujui.'
            );
    }

    /**
     * Admin meminta peserta memperbaiki dokumen.
     */
    public function revision(
        Request $request,
        ParticipantApplicationDocument $document
    ): RedirectResponse {
        /*
         * Pastikan dokumen dapat diperiksa.
         */
        abort_unless(
            in_array(
                $document->type,
                $this->reviewableTypes(),
                true
            ),
            404
        );

        /*
         * Catatan perbaikan wajib diisi.
         */
        $validated = $request->validate(
            [
                'review_notes' => [
                    'required',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'review_notes.required' => 'Catatan perbaikan wajib diisi.',
            ]
        );

        /*
         * Update status dokumen menjadi revision.
         */
        $document->update([
            'review_status' => ParticipantApplicationDocument::REVIEW_REVISION,

            'review_notes' => $validated['review_notes'],

            'reviewed_at' => now(),
        ]);

        /*
         * Update status aplikasi.
         *
         * Surat permohonan:
         * letter_revision_required
         *
         * Surat persetujuan etika:
         * ethics_revision_required
         */
        $document->application()->update([
            'status' => $document->type ===
                ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL
                    ? 'ethics_revision_required'
                    : 'letter_revision_required',
        ]);

        /*
         * Kirim notifikasi kepada peserta.
         */
        $this->notifyParticipant(
            $document,
            ParticipantApplicationDocument::REVIEW_REVISION
        );

        return redirect()
            ->route(
                'admin.pemeriksaan-dokumen.show',
                $document
            )
            ->with(
                'success',
                'Permintaan perbaikan berhasil dikirim kepada peserta.'
            );
    }

    public function preview(
        ParticipantApplicationDocument $document
    ) {
        abort_unless(
            in_array($document->type, $this->reviewableTypes(), true),
            404
        );

        abort_unless(
            Storage::disk('local')->exists($document->file_path),
            404
        );

        $path = Storage::disk('local')->path($document->file_path);

        return response()->file($path, [
            'Content-Type' => $document->mime_type ?? 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.
                addslashes(
                    $document->original_name
                    ?? basename($document->file_path)
                ).
                '"',
        ]);
    }

    public function download(
        ParticipantApplicationDocument $document
    ): StreamedResponse {
        abort_unless(
            in_array($document->type, $this->reviewableTypes(), true),
            404
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

    /**
     * Daftar tipe dokumen yang dapat diperiksa admin.
     *
     * @return list<string>
     */
    private function reviewableTypes(): array
    {
        return [
            ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
            ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL,
        ];
    }

    /**
     * Mengirim notifikasi hasil pemeriksaan kepada peserta.
     */
    private function notifyParticipant(
        ParticipantApplicationDocument $document,
        string $decision
    ): void {
        /*
         * Pastikan relasi application dan participant
         * sudah tersedia.
         */
        $document->loadMissing(
            'application.participant'
        );

        /*
         * Kirim notifikasi kepada peserta.
         */
        $document->application->participant->notify(
            new DocumentReviewUpdated(
                $document,
                $decision
            )
        );
    }
}
