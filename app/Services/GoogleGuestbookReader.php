<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\GuestbookPhone;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

class GoogleGuestbookReader
{
    public function hasResponse(string $phoneHash): bool
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
        $values = Http::withToken($token)->connectTimeout(5)->timeout(15)
            ->get($base.'/values/'.rawurlencode($parts[1].'!'.$phoneColumn.'2:'.$phoneColumn))
            ->throw()
            ->json('values', []);

        foreach ($values as $row) {
            $number = GuestbookPhone::normalize((string) ($row[0] ?? ''));
            if ($number !== null && hash_equals($phoneHash, GuestbookPhone::fingerprint($number))) {
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
