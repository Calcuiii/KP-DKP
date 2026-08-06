<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\Peserta\SaveParticipantApplicationRequest;
use App\Models\Participant;
use App\Models\ParticipantApplication;
use Illuminate\Http\RedirectResponse;

final class ParticipantApplicationController extends Controller
{
    public function store(SaveParticipantApplicationRequest $request): RedirectResponse
    {
        /** @var Participant $participant */
        $participant = $request->user('peserta');

        $application = $participant->applications()->latest()->first();

        if ($application instanceof ParticipantApplication) {
            $application->update($request->validated());
        } else {
            $participant->applications()->create([
                ...$request->validated(),
                'status' => 'preparation',
            ]);
        }

        return redirect()->route('peserta.dashboard')
            ->with('status', 'Persiapan pengajuan Anda telah disimpan.');
    }
}
