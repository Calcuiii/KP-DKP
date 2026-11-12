<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class ChatbotAiBudget
{
    public function reserve(string $scope, int $tokens): ?array
    {
        return DB::transaction(function () use ($scope, $tokens): ?array {
            $rows = $this->lockedPeriods($scope);
            foreach ($rows as $period => $row) {
                $requestLimit = min((int) config('chatbot.ai.requests_per_'.$period), $period === 'minute' ? 24 : 800);
                $tokenLimit = min((int) config('chatbot.ai.tokens_per_'.$period), $period === 'minute' ? 6400 : 160000);
                // Provider-reported limits can only tighten our conservative caps.
                $limits = json_decode($row->provider_limits ?? '{}', true);
                if ($period === 'day' && isset($limits['x-ratelimit-limit-requests'])) {
                    $requestLimit = min($requestLimit, (int) floor((int) $limits['x-ratelimit-limit-requests'] * .8));
                }
                if ($period === 'minute' && isset($limits['x-ratelimit-limit-tokens'])) {
                    $tokenLimit = min($tokenLimit, (int) floor((int) $limits['x-ratelimit-limit-tokens'] * .8));
                }
                if ($requestLimit <= 0 || $tokenLimit <= 0 || $row->requests >= $requestLimit || $row->tokens + $tokens > $tokenLimit) {
                    return null;
                }
            }
            $ids = array_map(fn ($row) => $row->id, $rows);
            DB::table('chatbot_ai_counters')->whereIn('id', $ids)->update([
                'requests' => DB::raw('requests + 1'), 'tokens' => DB::raw('tokens + '.$tokens), 'updated_at' => now(),
            ]);

            return ['ids' => array_values($ids), 'tokens' => $tokens];
        });
    }

    public function settle(array $reservation, bool $success, ?int $actual, ?string $error, array $headers): void
    {
        DB::transaction(function () use ($reservation, $success, $actual, $error, $headers): void {
            foreach ($reservation['ids'] as $id) {
                $row = DB::table('chatbot_ai_counters')->where('id', $id)->lockForUpdate()->first();
                if (! $row) {
                    continue;
                }
                $values = [
                    // Unknown usage (timeouts/errors) retains its entire reservation.
                    'tokens' => max(0, $row->tokens - $reservation['tokens']) + ($actual ?? $reservation['tokens']),
                    'actual_tokens' => $row->actual_tokens + ($actual ?? 0),
                    'successes' => $row->successes + (int) $success,
                    'failures' => $row->failures + (int) ! $success,
                    'last_error' => $error, 'updated_at' => now(),
                ];
                if ($headers !== []) {
                    $values['provider_limits'] = json_encode($headers, JSON_THROW_ON_ERROR);
                }
                DB::table('chatbot_ai_counters')->where('id', $id)->update($values);
            }
        });
    }

    public function note(string $scope, string $kind, ?string $error = null): void
    {
        if (! in_array($kind, ['cache_hits', 'fallbacks'], true)) {
            return;
        }
        DB::transaction(function () use ($scope, $kind, $error): void {
            $rows = $this->lockedPeriods($scope);
            DB::table('chatbot_ai_counters')->whereIn('id', array_map(fn ($row) => $row->id, $rows))
                ->update([$kind => DB::raw($kind.' + 1'), 'last_error' => $error, 'updated_at' => now()]);
        });
    }

    private function lockedPeriods(string $scope): array
    {
        $rows = [];
        foreach (['day', 'minute'] as $period) {
            $start = $period === 'day' ? now()->startOfDay() : now()->startOfMinute();
            $id = $scope.':'.$period.':'.$start->format('YmdHi');
            DB::table('chatbot_ai_counters')->insertOrIgnore([
                'id' => $id, 'scope' => $scope, 'period' => $period,
                'expires_at' => $period === 'day' ? $start->copy()->addDay() : $start->copy()->addMinute(),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $rows[$period] = DB::table('chatbot_ai_counters')->where('id', $id)->lockForUpdate()->first();
        }

        return $rows;
    }

    public function snapshot(): array
    {
        $scope = app(FreeGroqPolicy::class)->budgetScope();
        $row = DB::table('chatbot_ai_counters')->where('scope', $scope)->where('period', 'day')
            ->where('expires_at', '>', now())->latest('created_at')->first();
        $requests = (int) ($row?->requests ?? 0);
        $tokens = (int) ($row?->tokens ?? 0);
        $requestLimit = max(0, min(800, (int) config('chatbot.ai.requests_per_day')));
        $tokenLimit = max(0, min(160000, (int) config('chatbot.ai.tokens_per_day')));
        $providerLimits = json_decode($row?->provider_limits ?? '{}', true);
        if (isset($providerLimits['x-ratelimit-limit-requests'])) {
            $requestLimit = min($requestLimit, (int) floor((int) $providerLimits['x-ratelimit-limit-requests'] * .8));
        }

        return [
            'enabled' => (bool) config('services.groq.enabled') && app(FreeGroqPolicy::class)->allows(config('services.groq.api_key'), config('services.groq.model')),
            'requests' => $requests, 'request_limit' => $requestLimit,
            'tokens' => $tokens, 'token_limit' => $tokenLimit,
            'actual_tokens' => (int) ($row?->actual_tokens ?? 0),
            'failures' => (int) ($row?->failures ?? 0), 'cache_hits' => (int) ($row?->cache_hits ?? 0),
            'fallbacks' => (int) ($row?->fallbacks ?? 0), 'last_error' => $row?->last_error,
            'provider_limits' => $providerLimits,
            'warning' => $requestLimit === 0 || $tokenLimit === 0 || $requests >= $requestLimit * .8 || $tokens >= $tokenLimit * .8,
        ];
    }
}
