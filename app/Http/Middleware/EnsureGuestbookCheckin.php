<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureGuestbookCheckin
{
    public function handle(Request $request, Closure $next): Response|JsonResponse|RedirectResponse
    {
        if ($request->user('peserta') || $request->user('web') || $request->routeIs('chatbot')) {
            return $next($request);
        }
        $service = $request->session()->get('chatbot_guest_service');
        if ($service === 'wopps' || ($service === 'magang_pkl'
            && (int) $request->session()->get('guestbook_verified_until', 0) > now()->timestamp)) {
            return $next($request);
        }

        $destination = route($service === 'magang_pkl' ? 'guestbook.checkin' : 'chatbot.service');
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json(['message' => 'Pilih layanan untuk melanjutkan ke asisten.', 'guestbook_url' => $destination], 403);
        }

        return redirect()->guest($destination);
    }
}
