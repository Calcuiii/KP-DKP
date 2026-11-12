<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\GoogleGuestbookReader;
use App\Support\GuestbookPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class GuestbookCheckinController extends Controller
{
    public function selectService(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['service' => ['required', 'in:magang_pkl,wopps,none']]);
        if ($request->user('peserta') || $request->user('web')) {
            return $request->expectsJson() ? response()->json(['allowed' => true]) : to_route('chatbot');
        }
        if ($data['service'] !== 'magang_pkl' || $request->session()->get('chatbot_guest_service') !== 'magang_pkl') {
            $request->session()->forget('guestbook_pending');
        }
        $request->session()->put('chatbot_guest_service', $data['service'] === 'none' ? null : $data['service']);
        $allowed = $data['service'] === 'wopps' || ($data['service'] === 'magang_pkl'
            && (int) $request->session()->get('guestbook_verified_until', 0) > now()->timestamp);
        if ($request->expectsJson()) {
            return response()->json(['allowed' => $allowed, 'redirect' => $data['service'] === 'magang_pkl' && ! $allowed ? route('guestbook.checkin') : null]);
        }

        return to_route($data['service'] === 'magang_pkl' && ! $allowed ? 'guestbook.checkin' : 'chatbot');
    }

    public function show(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['html' => $this->card($request)]);
        }

        return view('pages.guestbook-checkin', [
            'guestbookUrl' => config('services.google_guestbook.form_url'),
            'pending' => $request->session()->get('guestbook_pending'),
        ]);
    }

    public function start(Request $request): RedirectResponse|JsonResponse
    {
        $request->session()->put('chatbot_guest_service', 'magang_pkl');
        $data = $request->validate(['phone' => ['required', 'string', 'max:32']]);
        $phone = GuestbookPhone::normalize($data['phone']);
        if ($phone === null) {
            throw ValidationException::withMessages(['phone' => 'Masukkan nomor telepon Indonesia yang valid, misalnya 081234567890.']);
        }
        if (! config('services.google_guestbook.form_url')) {
            return $this->verificationError($request, 'Tautan Google Form belum dikonfigurasi. Silakan hubungi pengelola.');
        }
        $request->session()->forget('guestbook_verified_until');
        $request->session()->put('guestbook_pending', [
            'phone_hash' => GuestbookPhone::fingerprint($phone),
            'phone_suffix' => substr($phone, -4),
            'expires_at' => now()->addMinutes(30)->timestamp,
        ]);

        return $request->expectsJson()
            ? response()->json(['allowed' => false, 'html' => $this->card($request)])
            : to_route('guestbook.checkin');
    }

    public function complete(Request $request, GoogleGuestbookReader $reader): RedirectResponse|JsonResponse
    {
        $pending = $request->session()->get('guestbook_pending');
        if (! is_array($pending) || ($pending['expires_at'] ?? 0) <= now()->timestamp) {
            $request->session()->forget('guestbook_pending');

            return $this->verificationError($request, 'Sesi verifikasi belum dimulai atau sudah kedaluwarsa. Masukkan nomor telepon kembali.');
        }
        try {
            $found = $reader->hasResponse($pending['phone_hash']);
        } catch (\Throwable $exception) {
            // HTTP exception messages can include response data. Log the type only.
            Log::warning('Guestbook verification unavailable.', ['exception_type' => get_class($exception)]);

            return $this->verificationError($request, 'Pemeriksaan Buku Tamu sedang tidak tersedia. Silakan coba lagi beberapa saat; akses asisten belum dibuka.', 503);
        }
        if (! $found) {
            return $this->verificationError($request, 'Nomor belum ditemukan pada Buku Tamu. Pastikan nomor yang dimasukkan sama dengan nomor pada Google Form, atau isi formulir jika belum pernah mengisi.');
        }
        $request->session()->forget('guestbook_pending');
        $request->session()->put('guestbook_verified_until', now()->addDay()->timestamp);
        $request->session()->put('chatbot_guest_service', 'magang_pkl');

        return $request->expectsJson()
            ? response()->json(['allowed' => true])
            : redirect()->intended(route('chatbot'));
    }

    private function card(Request $request): string
    {
        return view('components.guestbook.checkin-card', [
            'guestbookUrl' => config('services.google_guestbook.form_url'),
            'pending' => $request->session()->get('guestbook_pending'),
            'errors' => new ViewErrorBag,
        ])->render();
    }

    private function verificationError(Request $request, string $message, int $status = 422): RedirectResponse|JsonResponse
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message, 'html' => $this->card($request)], $status)
            : to_route('guestbook.checkin')->withErrors(['verification' => $message]);
    }
}
