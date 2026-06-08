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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'wa' => [
        'url' => env('WA_API_URL'),
    ],
    'feeder' => [
        'base_url' => env('FEEDER_API_URL'),
        'username' => env('FEEDER_USERNAME'),
        'password' => env('FEEDER_PASSWORD'),
        'sync' => [
            'batch_size' => (int) env('FEEDER_SYNC_BATCH_SIZE', 100),
            'delay_ms' => (int) env('FEEDER_SYNC_DELAY_MS', 200),
            'max_retries' => (int) env('FEEDER_MAX_RETRIES', 3),
        ],
    ],

];
