<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\ReplyLetter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class ReplyLetterController extends Controller
{
    public function index(): View
    {
        $participants = Participant::query()
            ->with('replyLetter')
            ->orderBy('name')
            ->paginate(10);

        return view(
            'pages.admin.surat-balasan.index',
            compact('participants')
        );
    }


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
        |--------------------------------------------------------------------------
        | Hapus surat lama jika ada
        |--------------------------------------------------------------------------
        */

        $existing = ReplyLetter::query()
            ->where('participant_id', $participant->id)
            ->first();

        if ($existing && $existing->file_path) {
            Storage::disk('public')
                ->delete($existing->file_path);
        }


        /*
        |--------------------------------------------------------------------------
        | Upload file baru
        |--------------------------------------------------------------------------
        */

        $file = $request->file('reply_letter');

        $path = $file->store(
            'reply-letters',
            'public'
        );


        /*
        |--------------------------------------------------------------------------
        | Simpan ke database
        |--------------------------------------------------------------------------
        */

        ReplyLetter::updateOrCreate(
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
        |--------------------------------------------------------------------------
        | TODO:
        | Buat notifikasi peserta di sini.
        |--------------------------------------------------------------------------
        */


        return back()->with(
            'success',
            'Surat balasan berhasil diunggah dan dikirim kepada peserta.'
        );
    }


    public function download(
        ReplyLetter $replyLetter
    ) {

        abort_unless(
            $replyLetter->file_path,
            404
        );

        return Storage::disk('public')->download(
            $replyLetter->file_path,
            $replyLetter->original_name
        );
    }
}