<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\ParticipantApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompletionFormProofController extends Controller
{
    public function store(Request $request, ParticipantApplication $application)
    {
        abort_unless($application->participant_id === $request->user('peserta')->id, 403);
        abort_unless($application->service_type === 'magang_pkl' && in_array($application->decision, ['accepted', 'approved', 'diterima'], true), 403);
        abort_unless($application->presentation_submitted_at, 422, 'Selesaikan tahap 6 terlebih dahulu.');
        $request->validate(['completion_form_proof' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120']]);
        $path = $request->file('completion_form_proof')->store('completion-form-proofs/'.$application->id, 'local');
        abort_unless(is_string($path), 500);
        try {
            $application->update(['completion_form_proof_path' => $path, 'completion_form_submitted_at' => now()]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect(route('peserta.dashboard').'#form-selesai')->with('status', 'Bukti pengisian form tersimpan. Tahap 7 selesai; silakan pantau penerimaan sertifikat pada tahap 8.');
    }

    public function download(Request $request, ParticipantApplication $application)
    {
        abort_unless($application->participant_id === $request->user('peserta')->id, 403);
        abort_unless($application->completion_form_proof_path && Storage::disk('local')->exists($application->completion_form_proof_path), 404);

        return Storage::disk('local')->download($application->completion_form_proof_path);
    }
}
