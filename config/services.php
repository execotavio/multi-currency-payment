<?php

return [

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

    'exchange_rate' => [
        'provider' => env('EXCHANGE_RATE_PROVIDER', 'exchangerate_api'),
        'base_url' => env('EXCHANGE_RATE_BASE_URL', 'https://v6.exchangerate-api.com/v6'),
        'api_key' => env('EXCHANGE_RATE_API_KEY'),
        'timeout' => (int) env('EXCHANGE_RATE_TIMEOUT', 5),
        'cache_ttl' => (int) env('EXCHANGE_RATE_CACHE_TTL', 3600),
        'cache_store' => env('EXCHANGE_RATE_CACHE_STORE', 'redis'),
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

];
