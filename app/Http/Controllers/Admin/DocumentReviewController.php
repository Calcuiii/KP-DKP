<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    public function index(): View
    {
        $documents = ParticipantApplicationDocument::query()
            ->whereIn('type', $this->reviewableTypes())
            ->with([
                'application.participant',
            ])
            ->latest()
            ->paginate(10);

        return view(
            'pages.admin.pemeriksaan-dokumen.index',
            compact('documents')
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

    /**
     * Admin menyetujui dokumen.
     *
     * Untuk surat permohonan:
     *
     * 1. Jika surat lengkap dan memiliki pernyataan permohonan
     *    penerbitan sertifikat:
     *    - Surat disetujui.
     *    - certificate_eligible = true.
     *    - Peserta dapat melanjutkan ke tahap berikutnya.
     *
     * 2. Jika surat tidak mencantumkan pernyataan permohonan
     *    penerbitan sertifikat dan admin mencentang
     *    "no_certificate":
     *    - Surat tetap disetujui.
     *    - certificate_eligible = false.
     *    - Peserta diberi notifikasi.
     *    - Peserta harus memilih:
     *        a. Upload ulang surat
     *        b. Lanjut tanpa upload ulang
     *
     * Untuk surat persetujuan etika:
     * - Surat tetap disetujui seperti biasa.
     */
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

        /*
         * Validasi input dari form admin.
         */
        $validated = $request->validate([
            'review_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            /*
             * Jika dokumen memiliki letter_group_key,
             * admin wajib mencocokkan identitas peserta.
             */
            'participant_identity_confirmed' => $document->letter_group_key
                ? ['accepted']
                : ['sometimes', 'accepted'],

            /*
             * Checkbox ini hanya akan digunakan untuk
             * surat permohonan.
             */
            'no_certificate' => [
                'sometimes',
                'boolean',
            ],
        ]);

        /*
         * Checkbox "no_certificate" hanya dianggap aktif
         * jika dokumen yang diperiksa adalah surat permohonan.
         *
         * Artinya:
         *
         * request_letter + checkbox dicentang
         * = surat disetujui tanpa pernyataan sertifikat.
         *
         * ethics_approval + checkbox
         * = checkbox diabaikan.
         */
        $noCertificate =
            $document->type === ParticipantApplicationDocument::TYPE_REQUEST_LETTER
            && $request->boolean('no_certificate');

        /*
         * Pesan khusus yang akan disampaikan kepada peserta
         * apabila surat tidak mencantumkan pernyataan sertifikat.
         */
        $noCertificateNotice =
            'Surat permohonan Anda telah disetujui, tetapi tidak mencantumkan '
            . 'pernyataan permohonan penerbitan sertifikat. Silakan pilih '
            . 'apakah ingin upload ulang surat atau tetap melanjutkan tanpa '
            . 'upload ulang.';

        /*
         * Gabungkan catatan admin dengan pemberitahuan khusus
         * apabila no_certificate aktif.
         */
        $finalNotes = $noCertificate
            ? trim(
                ($validated['review_notes'] ?? '')
                . "\n\n"
                . $noCertificateNotice
            )
            : ($validated['review_notes'] ?? null);

        /*
         * Update status dokumen.
         */
        $document->update([
            'review_status' => ParticipantApplicationDocument::REVIEW_APPROVED,

            'review_notes' => $finalNotes,

            'reviewed_at' => now(),

            /*
             * true:
             * surat memiliki/dianggap memenuhi pernyataan sertifikat.
             *
             * false:
             * admin menyetujui surat tetapi surat tidak mencantumkan
             * pernyataan permohonan penerbitan sertifikat.
             */
            'certificate_eligible' => ! $noCertificate,
        ]);

        /*
         * Update status aplikasi peserta.
         */
        $document->application()->update([
            'status' => $document->type === ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL
                ? 'ethics_approved'
                : (
                    $noCertificate
                        ? 'letter_approved_no_certificate'
                        : 'letter_approved'
                ),

            /*
             * Setiap kali admin melakukan approval,
             * pilihan tindak lanjut sebelumnya harus di-reset.
             *
             * Ini penting terutama ketika peserta sebelumnya
             * memilih "upload_again" kemudian surat baru diperiksa.
             */
            'certificate_follow_up_choice' => null,

            'certificate_follow_up_at' => null,
        ]);

        /*
         * Kirim notifikasi kepada peserta.
         *
         * Untuk no_certificate:
         * peserta akan mendapatkan informasi bahwa surat tetap
         * disetujui tetapi perlu memilih upload ulang atau lanjut.
         */
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
                $noCertificate
                    ? 'Surat berhasil disetujui. Peserta telah diberi pemberitahuan untuk memilih upload ulang atau melanjutkan tanpa upload ulang.'
                    : 'Surat berhasil disetujui.'
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
                'review_notes.required' =>
                    'Catatan perbaikan wajib diisi.',
            ]
        );

        /*
         * Update status dokumen menjadi revision.
         */
        $document->update([
            'review_status' =>
                ParticipantApplicationDocument::REVIEW_REVISION,

            'review_notes' =>
                $validated['review_notes'],

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
            'status' =>
                $document->type ===
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
            'Content-Disposition' => 'inline; filename="' .
                addslashes(
                    $document->original_name
                    ?? basename($document->file_path)
                ) .
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