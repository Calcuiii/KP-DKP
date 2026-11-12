<?php

return [
    'guest_per_minute' => (int) env('CHATBOT_GUEST_PER_MINUTE', 5),
    'guest_per_day' => (int) env('CHATBOT_GUEST_PER_DAY', 30),
    'account_per_minute' => (int) env('CHATBOT_ACCOUNT_PER_MINUTE', 10),
    'account_per_day' => (int) env('CHATBOT_ACCOUNT_PER_DAY', 100),
    'ip_per_minute' => (int) env('CHATBOT_IP_PER_MINUTE', 60),
    'ip_per_day' => (int) env('CHATBOT_IP_PER_DAY', 500),
    'ai' => [
        // A model name does not prove that the account is on a free plan.
        'account_owner' => env('GROQ_ACCOUNT_OWNER', 'unset'),
        'free_plan_confirmed' => (bool) env('GROQ_FREE_PLAN_CONFIRMED', false),
        'verified_key_hash' => env('GROQ_FREE_PLAN_KEY_SHA256', ''),
        'requests_per_minute' => (int) env('GROQ_REQUESTS_PER_MINUTE', 24),
        'requests_per_day' => (int) env('GROQ_REQUESTS_PER_DAY', 800),
        'tokens_per_minute' => (int) env('GROQ_TOKENS_PER_MINUTE', 6400),
        'tokens_per_day' => (int) env('GROQ_TOKENS_PER_DAY', 160000),
        'max_prompt_bytes' => (int) env('GROQ_MAX_PROMPT_BYTES', 4000),
        'cache_seconds' => (int) env('GROQ_ANSWER_CACHE_SECONDS', 900),
    ],
];
