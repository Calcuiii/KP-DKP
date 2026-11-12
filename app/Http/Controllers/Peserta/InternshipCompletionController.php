<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\ParticipantApplication;
use Illuminate\Http\Request;

class InternshipCompletionController extends Controller
{
    public function store(Request $request, ParticipantApplication $application)
    {
        abort_unless($request->user('peserta')->id === $application->participant_id, 403);
        abort_unless($application->canMarkInternshipCompleted(), 422, 'Selesaikan tahap 6 dan 7 serta tunggu tanggal akhir magang.');

        $updated = $application->newQuery()->whereKey($application->id)->whereNull('completed_at')
            ->update(['completed_at' => now(), 'status' => 'completed']);
        $request->session()->put('participant_active_application_id', $application->id);

        return redirect()->route('peserta.dashboard')->with('internship_celebration', $updated > 0);
    }
}
