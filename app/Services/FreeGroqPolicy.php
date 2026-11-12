<?php

namespace App\Services;

final class FreeGroqPolicy
{
    public const MODEL = 'openai/gpt-oss-20b';

    public function allows(?string $key, string $model): bool
    {
        $digest = (string) config('chatbot.ai.verified_key_hash');

        return $model === self::MODEL
            && is_string($key) && trim($key) !== ''
            && config('chatbot.ai.account_owner') === 'dkp'
            && config('chatbot.ai.free_plan_confirmed') === true
            && preg_match('/^[a-f0-9]{64}$/', $digest) === 1
            && hash_equals($digest, hash('sha256', $key));
    }

    public function scope(string $key): string
    {
        return substr(hash_hmac('sha256', $key, (string) config('app.key')), 0, 24);
    }

    public function budgetScope(): string
    {
        // Rotating credentials must not reset the website's daily budget.
        return substr(hash_hmac('sha256', 'si-melayur:groq-free-budget', (string) config('app.key')), 0, 24);
    }
}
