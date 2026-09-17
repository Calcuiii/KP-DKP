<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\ParticipantApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PresentationController extends Controller
{
    public function store(Request $request, ParticipantApplication $application)
    {
        abort_unless($application->participant_id === $request->user('peserta')->id, 403);
        abort_unless($application->service_type === 'magang_pkl' && in_array($application->decision, ['accepted', 'approved', 'diterima'], true), 403);
        $data = $request->validate([
            'presentation_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'presentation_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);
        $path = $request->file('presentation_photo')->store('presentation-proofs/'.$application->id, 'local');
        abort_unless(is_string($path), 500);
        try {
            $application->update(['presentation_date' => $data['presentation_date'], 'presentation_photo_path' => $path, 'presentation_submitted_at' => now()]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect(route('peserta.dashboard').'#penyelesaian')->with('status', 'Bukti presentasi tersimpan. Tahap 7 telah terbuka.');
    }

    public function download(Request $request, ParticipantApplication $application)
    {
        abort_unless($application->participant_id === $request->user('peserta')->id, 403);
        abort_unless($application->presentation_photo_path && Storage::disk('local')->exists($application->presentation_photo_path), 404);

        return Storage::disk('local')->download($application->presentation_photo_path);
    }

    public function profile(Request $request)
    {
        $data = $request->validate([
            'institution' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25', 'regex:/^\+?[0-9][0-9 ()-]{6,23}$/'],
        ]);
        $request->user('peserta')->update($data);

        return back()->with('status', 'Data institusi dan nomor telepon tersimpan.');
    }
}
