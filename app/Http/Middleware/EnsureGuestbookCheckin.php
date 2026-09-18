<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ParticipantApplication;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureGuestbookCheckin
{
    public function handle(Request $request, Closure $next): Response|JsonResponse|RedirectResponse
    {
        $participant = $request->user('peserta');
        $activeApplicationId = $request->session()->get('participant_active_application_id');
        $application = $participant && $activeApplicationId !== null
            ? $participant->applications()->whereKey($activeApplicationId)->first()
            : null;

        if ($application?->service_type === ParticipantApplication::SERVICE_WOPPS) {
            return $next($request);
        }

        if ($application?->service_type === ParticipantApplication::SERVICE_MAGANG_PKL
            && (int) $request->session()->get('guestbook_verified_until', 0) > now()->timestamp) {
            return $next($request);
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'message' => 'Silakan isi Buku Tamu sebelum menggunakan layanan chatbot.',
                'guestbook_url' => route('guestbook.checkin'),
            ], 403);
        }

        return redirect()->guest(route('guestbook.checkin'));
    }
}
