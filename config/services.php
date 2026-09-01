<?php

return [

    'google_guestbook' => [
        'credentials_path' => env('GOOGLE_SERVICE_ACCOUNT_PATH'),
        'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
        'range' => env('GOOGLE_SHEETS_RESPONSE_RANGE', "'Form Responses 1'!A:Z"),
        'phone_column' => env('GOOGLE_SHEETS_PHONE_COLUMN', 'Nomor Telepon'),
        'timestamp_column' => env('GOOGLE_SHEETS_TIMESTAMP_COLUMN', 'Timestamp'),
        'timezone' => env('GOOGLE_SHEETS_TIMEZONE', 'Asia/Jakarta'),
        'timestamp_format' => env('GOOGLE_SHEETS_TIMESTAMP_FORMAT', 'd/m/Y H:i:s'),
        'form_url' => env('GOOGLE_GUESTBOOK_FORM_URL'),
    ],

    'dkp' => [
        'internship_guestbook_url' => env('DKP_INTERNSHIP_GUESTBOOK_URL', 'https://bit.ly/DaftarMagangPKL_DKP_JATIM'),
        'contact_whatsapp_url' => env('DKP_CONTACT_WHATSAPP_URL'),
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_NOTIFICATIONS_ENABLED', false),
        'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v23.0'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'admin_recipient' => env('WHATSAPP_ADMIN_RECIPIENT'),
        'template_name' => env('WHATSAPP_TEMPLATE_NAME', 'simolek_unanswered_question'),
        'template_language' => env('WHATSAPP_TEMPLATE_LANGUAGE', 'id'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'groq' => [
        'enabled' => env('GROQ_ENABLED', false),
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
        'timeout_seconds' => (int) env('GROQ_TIMEOUT_SECONDS', 15),
        'max_completion_tokens' => (int) env('GROQ_MAX_COMPLETION_TOKENS', 1200),
    ],

];
