<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user('web')?->isSuperAdmin()) {
            abort(403, 'Fitur ini hanya dapat diakses oleh Super Admin.');
        }

        return $next($request);
    }
}
