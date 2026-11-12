<?php

return [
    // Store SHA-256 digests only. Keep raw tokens in the caller's secret manager.
    'token_hash' => env('SI_MELAYUR_ATTENDANCE_TOKEN_HASH'),
    'previous_token_hash' => env('SI_MELAYUR_ATTENDANCE_PREVIOUS_TOKEN_HASH'),
    'can_verify' => env('SI_MELAYUR_ATTENDANCE_CAN_VERIFY', false),
];
