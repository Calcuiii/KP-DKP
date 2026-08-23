<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ParticipantLoginRequest;
use App\Http\Requests\Auth\RegisterParticipantRequest;
use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\InternshipLocation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ParticipantAuthController extends Controller
{
    public function createLogin(): View
    {
        return view('pages.peserta.auth.login');
    }

    public function storeLogin(ParticipantLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('peserta.dashboard'));
    }

    public function createRegister(): View
    {
        return view('pages.peserta.auth.register');
    }

    public function storeRegister(RegisterParticipantRequest $request): RedirectResponse
    {
        $participant = Participant::query()->create($request->validated());

        event(new Registered($participant));

        Auth::guard('peserta')->login($participant);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('status', 'Akun berhasil dibuat. Periksa email Anda untuk memverifikasi akun.');
    }

    public function dashboard(): View
    {
        /** @var Participant $participant */
        $participant = Auth::guard('peserta')->user();

        $application = $participant->applications()->with('documents')->latest()->first();

        return view('pages.peserta.dashboard', [
            'application' => $application,
            'participantNotifications' => $participant->notifications()->latest()->limit(8)->get(),
            'unreadNotificationCount' => $participant->unreadNotifications()->count(),
            'serviceOptions' => ParticipantApplication::serviceOptions(),
            'internshipLocations' => InternshipLocation::query()->orderBy('display_order')->get(),
            'internshipGuestbookUrl' => config('services.dkp.internship_guestbook_url'),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('peserta')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('peserta.login');
    }
}
