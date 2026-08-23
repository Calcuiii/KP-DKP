<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

final class ParticipantPasswordResetController extends Controller
{
    public function create(): View
    {
        return view('pages.peserta.auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email']]);

        Password::broker('participants')->sendResetLink(['email' => $validated['email']]);

        return back()->with('status', 'Jika email terdaftar, tautan untuk mengatur ulang kata sandi akan segera dikirim. Silakan periksa kotak masuk dan folder spam.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('pages.peserta.auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::broker('participants')->reset(
            $validated,
            function (Participant $participant, string $password): void {
                $participant->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($participant));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Tautan tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.',
            ]);
        }

        return redirect()->route('peserta.login')->with('status', 'Kata sandi berhasil diperbarui. Silakan masuk menggunakan kata sandi baru.');
    }
}
