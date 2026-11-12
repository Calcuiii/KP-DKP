<?php

use App\Http\Middleware\EnsureGuestbookCheckin;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->trustHosts(at: function (): array {
            $host = parse_url((string) config('app.url'), PHP_URL_HOST);

            return app()->isProduction() && is_string($host)
                ? ['^'.preg_quote($host, '/').'$'] : [];
        }, subdomains: false);
        // Guestbook access must be checked before implicit model binding.
        // Otherwise an unverified API request can query a bound model first
        // and leak a 404/500 response instead of consistently returning 403.
        $middleware->prependToPriorityList(
            SubstituteBindings::class,
            EnsureGuestbookCheckin::class,
        );

        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '127.0.0.1'));

        $middleware->redirectGuestsTo(function (Request $request): string {
            return $request->is('peserta/*') || $request->is('akun/*')
                ? route('peserta.login')
                : route('admin.login');
        });

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'superadmin' => EnsureUserIsSuperAdmin::class,
            'guestbook' => EnsureGuestbookCheckin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportWhen(fn (Throwable $exception) => request()->is('api/participants/verify'));
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/participants/verify')) {
                return null;
            }
            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode() : 500;
            $code = match ($status) {
                429 => 'RATE_LIMITED',
                405 => 'METHOD_NOT_ALLOWED',
                default => 'SERVICE_UNAVAILABLE',
            };
            $response = response()->json(['error' => ['code' => $code, 'message' => 'Request could not be processed.']], $status)
                ->header('Cache-Control', 'no-store');
            if ($status === 429) {
                $response->withHeaders($exception->getHeaders());
            }

            return $response;
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
