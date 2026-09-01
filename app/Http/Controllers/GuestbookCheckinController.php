<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\GoogleGuestbookReader;
use App\Support\GuestbookPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class GuestbookCheckinController extends Controller
{
    public function show(Request $request): View
    {
        return view('pages.guestbook-checkin', [
            'guestbookUrl' => config('services.google_guestbook.form_url'),
            'pending' => $request->session()->get('guestbook_pending'),
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:32']]);
        $phone = GuestbookPhone::normalize($data['phone']);
        if ($phone === null) {
            throw ValidationException::withMessages(['phone' => 'Masukkan nomor telepon Indonesia yang valid, misalnya 081234567890.']);
        }
        if (! config('services.google_guestbook.form_url')) {
            return back()->withErrors(['verification' => 'Tautan Google Form belum dikonfigurasi. Silakan hubungi pengelola.']);
        }
        $request->session()->forget('guestbook_verified_until');
        $request->session()->put('guestbook_pending', [
            'phone_hash' => GuestbookPhone::fingerprint($phone),
            'phone_suffix' => substr($phone, -4),
            'requested_at' => now()->timestamp,
            'expires_at' => now()->addMinutes(30)->timestamp,
        ]);

        return to_route('guestbook.checkin');
    }

    public function complete(Request $request, GoogleGuestbookReader $reader): RedirectResponse
    {
        $pending = $request->session()->get('guestbook_pending');
        if (! is_array($pending) || ($pending['expires_at'] ?? 0) <= now()->timestamp) {
            $request->session()->forget('guestbook_pending');

            return to_route('guestbook.checkin')->withErrors(['verification' => 'Sesi verifikasi belum dimulai atau sudah kedaluwarsa. Masukkan nomor telepon kembali.']);
        }
        try {
            $found = $reader->hasRecentResponse($pending['phone_hash'], $pending['requested_at']);
        } catch (\Throwable $exception) {
            // HTTP exception messages can include response data. Log the type only.
            Log::warning('Guestbook verification unavailable.', ['exception_type' => get_class($exception)]);

            return to_route('guestbook.checkin')->withErrors(['verification' => 'Pemeriksaan Buku Tamu sedang tidak tersedia. Silakan coba lagi beberapa saat; akses asisten belum dibuka.']);
        }
        if (! $found) {
            return to_route('guestbook.checkin')->withErrors(['verification' => 'Pengisian baru belum ditemukan. Kirim Google Form dengan nomor yang sama setelah memulai verifikasi, lalu tunggu beberapa detik dan periksa kembali.']);
        }
        $request->session()->forget('guestbook_pending');
        $request->session()->put('guestbook_verified_until', now()->addDay()->timestamp);

        return redirect()->intended(route('chatbot'));
    }
}
