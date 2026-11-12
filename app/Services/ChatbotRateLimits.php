<?php

namespace App\Services;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

final class ChatbotRateLimits
{
    public function actor(Request $request): string
    {
        $identity = $request->user('peserta') ? 'participant:'.$request->user('peserta')->getAuthIdentifier()
            : ($request->user('web') ? 'admin:'.$request->user('web')->getAuthIdentifier() : 'guest:'.$request->session()->getId());

        return hash_hmac('sha256', $identity, (string) config('app.key'));
    }

    public function messages(Request $request): array
    {
        $account = $request->user('peserta') || $request->user('web');
        $prefix = $account ? 'account' : 'guest';
        $actor = $this->actor($request);
        $ip = hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));
        $response = fn (Request $request, array $headers) => response()->json([
            'message' => 'Batas pertanyaan tercapai. Silakan tunggu sebelum mengirim pertanyaan berikutnya; panduan tetap dapat dibuka.',
            'code' => 'CHATBOT_MESSAGE_LIMIT', 'retry_after_seconds' => (int) ($headers['Retry-After'] ?? 60),
        ], 429, $headers);

        return [
            Limit::perMinute(max(1, (int) config('chatbot.'.$prefix.'_per_minute')))->by('messages:minute:'.$actor)->response($response),
            Limit::perDay(max(1, (int) config('chatbot.'.$prefix.'_per_day')))->by('messages:day:'.$actor)->response($response),
            Limit::perMinute(max(1, (int) config('chatbot.ip_per_minute')))->by('messages:ip:minute:'.$ip)->response($response),
            Limit::perDay(max(1, (int) config('chatbot.ip_per_day')))->by('messages:ip:day:'.$ip)->response($response),
        ];
    }
}
