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
    'paystack' => [
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
    ],

    'flutterwave' => [
        'secret' => env('FLUTTERWAVE_SECRET_KEY'),
        'base_url' => env('FLUTTERWAVE_BASE_URL', 'https://api.flutterwave.com/v3'),
        'currency' => env('FLUTTERWAVE_CURRENCY', 'NGN'),
        'redirect_url' => env('FLUTTERWAVE_REDIRECT_URL'),
        'webhook_secret_hash' => env('FLUTTERWAVE_WEBHOOK_SECRET_HASH'),
    ],

    'sendchamp' => [
        'token' => env('SENDCHAMP_API_KEY'),
        'sender_name' => env('SENDCHAMP_SENDER_NAME', 'Sendchamp'),
        'route' => env('SENDCHAMP_ROUTE', 'dnd'),
        'base_url' => env('SENDCHAMP_BASE_URL', 'https://api.sendchamp.com/api/v1'),
    ],

    'mapbox' => [
        'public_token' => env('NEXT_PUBLIC_MAPBOX_TOKEN', env('VITE_MAPBOX_TOKEN')),
    ],

];
