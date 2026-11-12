<?php

namespace App\KnowledgeBase;

use App\Services\ChatbotAiBudget;
use App\Services\FreeGroqPolicy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Throwable;

final class BudgetedGroqAnswerGenerator implements KnowledgeBaseAnswerGenerator
{
    public function __construct(private readonly GroqKnowledgeBaseAnswerGenerator $inner) {}

    public function generate(KnowledgeBaseGroundedContext $context): KnowledgeBaseAnswerGenerationResult
    {
        $key = config('services.groq.api_key');
        $policy = app(FreeGroqPolicy::class);
        if (! config('services.groq.enabled') || ! $policy->allows($key, config('services.groq.model'))) {
            return KnowledgeBaseAnswerGenerationResult::providerFailure('free_account_not_verified');
        }
        $scope = $policy->budgetScope();
        $cacheScope = $policy->scope($key);
        $budget = app(ChatbotAiBudget::class);
        try {
            $digest = hash_hmac('sha256', json_encode([$context, config('services.groq.model'),
                config('services.groq.max_completion_tokens'), $this->inner->promptFingerprint($context)], JSON_THROW_ON_ERROR), (string) config('app.key'));
            $cacheKey = 'chatbot-ai:'.$cacheScope.':'.$digest;
            // Includes full selected context and hashes, so edits/deletions immediately stop reuse.
            $cached = Cache::get($cacheKey);
            if (is_string($cached)) {
                $answer = json_decode(Crypt::decryptString($cached), true, 512, JSON_THROW_ON_ERROR);
                $budget->note($scope, 'cache_hits');

                return KnowledgeBaseAnswerGenerationResult::success($answer['answer'], $answer['sources']);
            }
            if ($this->inner->promptBytes($context) > min(4000, max(0, (int) config('chatbot.ai.max_prompt_bytes')))) {
                return $this->fallback($scope, 'context_budget_exceeded');
            }
            if (Cache::has('chatbot-ai:cooldown:'.$scope)) {
                return $this->fallback($scope, 'provider_cooldown');
            }
            $result = Cache::lock($cacheKey.':lock', 30)->get(function () use ($scope, $budget, $context, $cacheKey) {
                $reservation = $budget->reserve($scope, $this->inner->reservedTokens($context));
                if ($reservation === null) {
                    return $this->fallback($scope, 'local_ai_budget_exhausted');
                }
                $result = $this->inner->generate($context);
                $success = $result->status !== KnowledgeBaseAnswerGenerationResult::STATUS_PROVIDER_FAILURE;
                $budget->settle($reservation, $success, $result->tokensUsed, $result->failureCode, $result->providerLimits);
                if (! $success) {
                    $budget->note($scope, 'fallbacks', $result->failureCode);
                    $seconds = match ($result->failureCode) {
                        'provider_rate_limited' => max(1, min(86400, $result->retryAfter ?? 60)),
                        'provider_credentials_invalid', 'provider_forbidden', 'provider_model_unavailable' => 600,
                        default => 15,
                    };
                    Cache::put('chatbot-ai:cooldown:'.$scope, true, $seconds);
                } elseif ($result->status === KnowledgeBaseAnswerGenerationResult::STATUS_SUCCESS) {
                    $ttl = min(900, max(0, (int) config('chatbot.ai.cache_seconds')));
                    if ($ttl > 0) {
                        Cache::put($cacheKey, Crypt::encryptString(json_encode([
                            'answer' => $result->answer, 'sources' => $result->sources,
                        ], JSON_THROW_ON_ERROR)), $ttl);
                    }
                }

                return $result;
            });

            return $result ?: $this->fallback($scope, 'identical_request_in_progress');
        } catch (Throwable) {
            // Budget/cache/database failure must never permit an unmetered API call.
            return KnowledgeBaseAnswerGenerationResult::providerFailure('provider_controls_unavailable');
        }
    }

    private function fallback(string $scope, string $reason): KnowledgeBaseAnswerGenerationResult
    {
        app(ChatbotAiBudget::class)->note($scope, 'fallbacks', $reason);

        return KnowledgeBaseAnswerGenerationResult::providerFailure($reason);
    }
}
