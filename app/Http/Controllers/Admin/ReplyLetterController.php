<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\ReplyLetter;
use App\Models\ParticipantApplicationDocument;
use App\Notifications\ReplyLetterSent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class ReplyLetterController extends Controller
{
    /**
     * Daftar peserta yang sudah mengirim bukti pengisian
     * Google Form Magang/PKL.
     */
    public function index(): View
    {
        $participants = Participant::query()
            ->with([
                'replyLetter',
                'applications.documents',
            ])
            ->whereHas('applications.documents', function ($query) {
                $query->where(
                    'type',
                    ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF
                );
            })
            ->orderBy('name')
            ->paginate(10);

        return view(
            'pages.admin.surat-balasan.index',
            compact('participants')
        );
    }

    /**
     * Admin mengunggah dan mengirim surat balasan
     * kepada peserta.
     */
    public function upload(
        Request $request,
        Participant $participant
    ): RedirectResponse {
        $request->validate([
            'reply_letter' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ], [
            'reply_letter.required' =>
                'Surat balasan wajib diunggah.',

            'reply_letter.mimes' =>
                'Surat balasan harus berupa file PDF.',

            'reply_letter.max' =>
                'Ukuran surat maksimal 10 MB.',
        ]);

        /*
         * Cari surat balasan lama milik peserta.
         */
        $existing = ReplyLetter::query()
            ->where('participant_id', $participant->id)
            ->first();

        /*
         * Jika sudah ada surat lama,
         * hapus file lama dari storage.
         */
        if ($existing && $existing->file_path) {
            Storage::disk('public')->delete(
                $existing->file_path
            );
        }

        /*
         * Simpan file baru.
         */
        $file = $request->file('reply_letter');

        $path = $file->store(
            'reply-letters',
            'public'
        );

        /*
         * Simpan data surat balasan.
         */
        $replyLetter = ReplyLetter::updateOrCreate(
            [
                'participant_id' => $participant->id,
            ],
            [
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'sent_at' => now(),
            ]
        );

        /*
         * Kirim notifikasi database
         * kepada peserta.
         */
        $participant->notify(
            new ReplyLetterSent($replyLetter)
        );

        return redirect()
    ->route('admin.surat-balasan')
    ->with(
        'success',
        'Surat balasan berhasil diunggah dan dikirim kepada peserta.'
    );
    }

    /**
     * Download surat balasan.
     */
    public function download(
        ReplyLetter $replyLetter
    ) {
        abort_unless(
            filled($replyLetter->file_path),
            404
        );

        abort_unless(
            Storage::disk('public')->exists(
                $replyLetter->file_path
            ),
            404
        );

        return Storage::disk('public')->download(
            $replyLetter->file_path,
            $replyLetter->original_name
        );
    }

    /**
     * Preview surat balasan di browser.
     */
    public function preview(
        ReplyLetter $replyLetter
    ) {
        abort_unless(
            filled($replyLetter->file_path),
            404
        );

        abort_unless(
            Storage::disk('public')->exists(
                $replyLetter->file_path
            ),
            404
        );

        $path = Storage::disk('public')->path(
            $replyLetter->file_path
        );

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>
                'inline; filename="' .
                addslashes(
                    $replyLetter->original_name
                    ?? basename($replyLetter->file_path)
                ) .
                '"',
        ]);
    }

    /**
     * Preview bukti pengisian Google Form di browser.
     */
    public function previewProof(
        ParticipantApplicationDocument $document
    ) {
        abort_unless(
            $document->type === ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF,
            404
        );

        abort_unless(
            filled($document->file_path),
            404
        );

        abort_unless(
            Storage::disk('local')->exists(
                $document->file_path
            ),
            404
        );

        $path = Storage::disk('local')->path(
            $document->file_path
        );

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
}