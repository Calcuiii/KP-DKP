<?php

declare(strict_types=1);

namespace App\Support;

final class GuestbookPhone
{
    public static function normalize(string $value): ?string
    {
        if (! preg_match('/^\+?[0-9\s().-]+$/D', trim($value))) {
            return null;
        }
        $number = preg_replace('/\D/', '', $value);
        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        } elseif (str_starts_with($number, '8')) {
            $number = '62'.$number;
        }

        return preg_match('/^62[1-9][0-9]{7,12}$/D', $number) ? $number : null;
    }

    public static function fingerprint(string $number): string
    {
        return hash_hmac('sha256', $number, (string) config('app.key'));
    }
}
