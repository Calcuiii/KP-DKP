<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\ParticipantApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function download(Request $request, ParticipantApplication $application)
    {
        abort_unless($request->user('peserta')?->id === $application->participant_id, 403);
        abort_unless($application->presentation_submitted_at && $application->completion_form_submitted_at, 403);
        abort_unless($application->certificate_path && Storage::disk('local')->exists($application->certificate_path), 404);

        return Storage::disk('local')->download($application->certificate_path, 'sertifikat-magang.pdf');
    }
}
