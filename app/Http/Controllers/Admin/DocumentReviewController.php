<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParticipantApplicationDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentReviewController extends Controller
{
    /**
     * Daftar surat permohonan yang perlu diperiksa.
     */
    public function index(): View
    {
        $documents = ParticipantApplicationDocument::query()
            ->where('type', ParticipantApplicationDocument::TYPE_REQUEST_LETTER)
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
            $document->type === ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
            404
        );

        $document->load([
            'application.participant',
        ]);

        return view('pages.admin.pemeriksaan-dokumen.show', compact('document'));
    }

    /**
     * Admin menyetujui surat.
     */
    public function approve(
        Request $request,
        ParticipantApplicationDocument $document
    ): RedirectResponse {
        abort_unless(
            $document->type === ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
            404
        );

        $document->update([
            'review_status' => 'review_approved',
            'review_notes' => $request->input('review_notes'),
        ]);

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
            $document->type === ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
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
        ]);

        return redirect()
            ->route('admin.pemeriksaan-dokumen.show', $document)
            ->with('success', 'Permintaan perbaikan berhasil dikirim kepada peserta.');
    }

    public function download(ParticipantApplicationDocument $document): \Symfony\Component\HttpFoundation\BinaryFileResponse
{
    abort_unless(
        $document->type === ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
        404
    );

    $absolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($document->file_path);

    abort_unless(file_exists($absolutePath), 404);

    return response()->file($absolutePath, [
        'Content-Type' => $document->mime_type ?? 'application/pdf',
    ]);
}
}