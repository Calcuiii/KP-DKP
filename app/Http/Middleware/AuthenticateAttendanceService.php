<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateAttendanceService
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $valid = false;
        foreach (['token_hash', 'previous_token_hash'] as $key) {
            $expected = config('attendance.'.$key);
            if (is_string($expected) && preg_match('/^[a-f0-9]{64}$/', $expected)
                && is_string($token) && strlen($token) >= 32 && strlen($token) <= 512) {
                $valid = hash_equals($expected, hash('sha256', $token)) || $valid;
            }
        }
        if (! $valid) {
            return response()->json(['error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Invalid integration credentials.']], 401)
                ->header('WWW-Authenticate', 'Bearer')->header('Cache-Control', 'no-store');
        }
        if (! config('attendance.can_verify')) {
            return response()->json(['error' => ['code' => 'FORBIDDEN', 'message' => 'Participant verification is not permitted.']], 403)
                ->header('Cache-Control', 'no-store');
        }
        if (app()->environment('production') && ! $request->isSecure()) {
            return response()->json(['error' => ['code' => 'HTTPS_REQUIRED', 'message' => 'HTTPS is required.']], 403)
                ->header('Cache-Control', 'no-store');
        }

        return $next($request);
    }
}
