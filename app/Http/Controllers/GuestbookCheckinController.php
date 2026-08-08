<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class GuestbookCheckinController extends Controller
{
    public const COOKIE_NAME = 'dkp_guestbook_completed';

    private const COOKIE_MINUTES = 60 * 24 * 180;

    public function show(): View
    {
        return view('pages.guestbook-checkin', [
            'guestbookUrl' => config('services.dkp.internship_guestbook_url'),
        ]);
    }

    public function complete(): RedirectResponse
    {
        return redirect()
            ->intended(route('chatbot'))
            ->withCookie(cookie(
                self::COOKIE_NAME,
                '1',
                self::COOKIE_MINUTES,
                path: '/',
                secure: null,
                httpOnly: true,
                raw: false,
                sameSite: 'lax',
            ));
    }
}
