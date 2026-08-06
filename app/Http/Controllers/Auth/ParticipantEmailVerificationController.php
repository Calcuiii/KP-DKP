<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ParticipantEmailVerificationController extends Controller
{
    public function notice(): View
    {
        /** @var Participant $participant */
        $participant = Auth::guard('peserta')->user();

        return view('pages.peserta.auth.verify-email');
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        /** @var Participant $participant */
        $participant = Auth::guard('peserta')->user();

        abort_unless($participant->getKey() === $id, 403);
        abort_unless(hash_equals(sha1($participant->getEmailForVerification()), $hash), 403);

        if (! $participant->hasVerifiedEmail()) {
            $participant->markEmailAsVerified();
            event(new Verified($participant));
        }

        return redirect()->route('verification.notice')
            ->with('status', 'Email berhasil diverifikasi. Akun Anda siap digunakan.');
    }

    public function send(Request $request): RedirectResponse
    {
        /** @var Participant $participant */
        $participant = Auth::guard('peserta')->user();

        if ($participant->hasVerifiedEmail()) {
            return redirect()->route('peserta.dashboard');
        }

        $participant->sendEmailVerificationNotification();

        return back()->with('status', 'Tautan verifikasi baru telah dikirim ke email Anda.');
    }
}
