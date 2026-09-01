<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\GuestbookPhone;
use Carbon\CarbonImmutable;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

class GoogleGuestbookReader
{
    public function hasRecentResponse(string $phoneHash, int $requestedAt): bool
    {
        if (RateLimiter::tooManyAttempts('google-guestbook-reads', 40)) {
            throw new RuntimeException('Guestbook lookup busy.');
        }
        RateLimiter::hit('google-guestbook-reads', 60);
        $id = (string) config('services.google_guestbook.spreadsheet_id');
        $range = (string) config('services.google_guestbook.range');
        if (! preg_match('/^[a-zA-Z0-9_-]+$/D', $id)
            || ! preg_match('/^(.+)!A:([A-Z]+)$/D', $range, $parts)) {
            throw new RuntimeException('Guestbook configuration missing.');
        }
        $token = $this->accessToken();
        $base = 'https://sheets.googleapis.com/v4/spreadsheets/'.$id;
        $headers = Http::withToken($token)->connectTimeout(5)->timeout(15)
            ->get($base.'/values/'.rawurlencode($parts[1].'!A1:'.$parts[2].'1'))
            ->throw()->json('values.0', []);
        $phoneColumn = $this->column($headers, (string) config('services.google_guestbook.phone_column'));
        $timeColumn = $this->column($headers, (string) config('services.google_guestbook.timestamp_column'));
        $query = 'ranges='.rawurlencode($parts[1].'!'.$phoneColumn.'2:'.$phoneColumn)
            .'&ranges='.rawurlencode($parts[1].'!'.$timeColumn.'2:'.$timeColumn)
            .'&valueRenderOption=UNFORMATTED_VALUE&dateTimeRenderOption=SERIAL_NUMBER';
        $values = Http::withToken($token)->connectTimeout(5)->timeout(15)
            ->get($base.'/values:batchGet?'.$query)->throw()->json('valueRanges', []);
        if (count($values) !== 2) {
            throw new RuntimeException('Invalid guestbook response.');
        }
        foreach ($values[0]['values'] ?? [] as $index => $row) {
            $number = GuestbookPhone::normalize((string) ($row[0] ?? ''));
            if ($number === null || ! hash_equals($phoneHash, GuestbookPhone::fingerprint($number))) {
                continue;
            }
            $timestamp = $this->timestamp($values[1]['values'][$index][0] ?? null);
            if ($timestamp !== null && $timestamp >= $requestedAt && $timestamp <= now()->timestamp) {
                return true;
            }
        }

        return false;
    }

    private function column(array $headers, string $name): string
    {
        $matches = array_keys(array_map('trim', $headers), trim($name), true);
        if ($name === '' || count($matches) !== 1) {
            throw new RuntimeException('Guestbook header missing or duplicated.');
        }
        $index = $matches[0] + 1;
        $column = '';
        while ($index > 0) {
            $index--;
            $column = chr(65 + $index % 26).$column;
            $index = intdiv($index, 26);
        }

        return $column;
    }

    private function timestamp(mixed $value): ?int
    {
        $timezone = (string) config('services.google_guestbook.timezone', 'Asia/Jakarta');
        if (is_numeric($value) && (float) $value > 0 && (float) $value < 100000) {
            // Sheets serials represent local wall time, not elapsed UTC time since 1899.
            $wallTime = CarbonImmutable::create(1899, 12, 30, 0, 0, 0, 'UTC')
                ->addSeconds((int) round((float) $value * 86400))->format('Y-m-d H:i:s');

            return CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $wallTime, $timezone)->timestamp;
        }
        if (! is_string($value)) {
            return null;
        }
        try {
            $format = (string) config('services.google_guestbook.timestamp_format');
            $date = CarbonImmutable::createFromFormat('!'.$format, $value, $timezone);

            return $date && $date->format($format) === $value ? $date->timestamp : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function accessToken(): string
    {
        $path = (string) config('services.google_guestbook.credentials_path');
        if ($path === '') {
            throw new RuntimeException('Guestbook credentials not configured.');
        }
        $path = str_starts_with($path, '/') ? $path : base_path($path);
        if (! is_readable($path)) {
            throw new RuntimeException('Guestbook credentials unavailable.');
        }
        $json = file_get_contents($path);
        $credentials = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (($credentials['type'] ?? '') !== 'service_account') {
            throw new RuntimeException('Invalid guestbook credentials.');
        }

        // Google's library signs the assertion. Do not log credentials or tokens.
        return Cache::remember('guestbook-token:'.hash('sha256', $json), 3000, function () use ($credentials): string {
            $auth = new ServiceAccountCredentials('https://www.googleapis.com/auth/spreadsheets.readonly', $credentials);
            $token = $auth->fetchAuthToken(function (RequestInterface $request): Response {
                $response = Http::connectTimeout(5)->timeout(15)
                    ->withHeaders($request->getHeaders())
                    ->withBody((string) $request->getBody(), 'application/x-www-form-urlencoded')
                    ->send($request->getMethod(), (string) $request->getUri());

                return new Response($response->status(), $response->headers(), $response->body());
            });
            if (empty($token['access_token'])) {
                throw new RuntimeException('Guestbook authentication failed.');
            }

            return $token['access_token'];
        });
    }
}
