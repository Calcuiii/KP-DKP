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
     * Daftar surat permohonan yang perlu diperiksa.
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

        return view('pages.admin.pemeriksaan-dokumen.index', compact('documents'));
    }

    /**
     * Menampilkan detail surat.
     */
    public function show(
        ParticipantApplicationDocument $document
    ): View {
        abort_unless(
            in_array($document->type, $this->reviewableTypes(), true),
            404
        );

        $document->load([
            'application.participant',
        ]);

        $relatedLetters = ParticipantApplicationDocument::query()
            ->where('type', ParticipantApplicationDocument::TYPE_REQUEST_LETTER)
            ->where('letter_group_key', $document->letter_group_key)
            ->whereNotNull('letter_group_key')
            ->whereHas('application', fn ($query) => $query->where('service_type', $document->application->service_type))
            ->whereRaw('version = (select max(latest.version) from participant_application_documents as latest where latest.participant_application_id = participant_application_documents.participant_application_id and latest.type = participant_application_documents.type)')
            ->with('application.participant')
            ->get();

        return view('pages.admin.pemeriksaan-dokumen.show', compact('document', 'relatedLetters'));
    }

    /**
     * Admin menyetujui surat.
     */
    public function approve(
        Request $request,
        ParticipantApplicationDocument $document
    ): RedirectResponse {
        abort_unless(
            in_array($document->type, $this->reviewableTypes(), true),
            404
        );

        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:2000'],
            'participant_identity_confirmed' => $document->letter_group_key ? ['accepted'] : ['sometimes', 'accepted'],
        ]);

        $document->update([
            'review_status' => ParticipantApplicationDocument::REVIEW_APPROVED,
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_at' => now(),
        ]);
        $document->application()->update(['status' => $document->type === ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL ? 'ethics_approved' : 'letter_approved']);
        $this->notifyParticipant($document, ParticipantApplicationDocument::REVIEW_APPROVED);

        return redirect()
            ->route('admin.pemeriksaan-dokumen.show', $document)
            ->with('success', 'Surat berhasil disetujui.');
    }

    /**
     * Admin meminta peserta memperbaiki surat.
     */
    public function revision(
        Request $request,
        ParticipantApplicationDocument $document
    ): RedirectResponse {
        abort_unless(
            in_array($document->type, $this->reviewableTypes(), true),
            404
        );

        $validated = $request->validate([
            'review_notes' => [
                'required',
                'string',
                'max:2000',
            ],
        ], [
            'review_notes.required' => 'Catatan perbaikan wajib diisi.',
        ]);

        $document->update([
            'review_status' => ParticipantApplicationDocument::REVIEW_REVISION,
            'review_notes' => $validated['review_notes'],
            'reviewed_at' => now(),
        ]);
        $document->application()->update(['status' => $document->type === ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL ? 'ethics_revision_required' : 'letter_revision_required']);
        $this->notifyParticipant($document, ParticipantApplicationDocument::REVIEW_REVISION);

        return redirect()
            ->route('admin.pemeriksaan-dokumen.show', $document)
            ->with('success', 'Permintaan perbaikan berhasil dikirim kepada peserta.');
    }

    public function download(ParticipantApplicationDocument $document): StreamedResponse
    {
        abort_unless(
            in_array($document->type, $this->reviewableTypes(), true),
            404
        );

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    /** @return list<string> */
    private function reviewableTypes(): array
    {
        return [
            ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
            ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL,
        ];
    }

    private function notifyParticipant(ParticipantApplicationDocument $document, string $decision): void
    {
        $document->loadMissing('application.participant');
        $document->application->participant->notify(new DocumentReviewUpdated($document, $decision));
    }
}
