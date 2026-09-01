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
        if ((int) $request->session()->get('guestbook_verified_until', 0) > now()->timestamp) {
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
