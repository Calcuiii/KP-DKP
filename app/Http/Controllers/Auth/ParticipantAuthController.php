<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ParticipantLoginRequest;
use App\Http\Requests\Auth\RegisterParticipantRequest;
use App\Models\InternshipLocation;
use App\Models\Participant;
use App\Models\ParticipantApplication;
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

    public function dashboard(Request $request): View
    {
        /** @var Participant $participant */
        $participant = Auth::guard('peserta')->user();

        $applications = $participant->applications()->with('documents')->latest()->get();
        $selectedApplicationId = $request->session()->get('participant_active_application_id');

        if ($selectedApplicationId !== null && $applications->contains('id', (int) $selectedApplicationId)) {
            $application = $applications->firstWhere('id', (int) $selectedApplicationId);
        } else {
            $application = $applications->first();
            $request->session()->put('participant_active_application_id', $application?->id);
        }

        if (request()->routeIs('peserta.activities')) {
            abort_unless($application
                && $application->service_type === ParticipantApplication::SERVICE_MAGANG_PKL
                && ($application->official_started_at !== null || in_array(strtolower((string) $application->decision), ['accepted', 'approved', 'diterima'], true)), 403);
        }

        return view('pages.peserta.dashboard', [
            'application' => $application,
            'applications' => $applications,
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
