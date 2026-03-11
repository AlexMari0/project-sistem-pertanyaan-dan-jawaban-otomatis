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

    'flask_api' => [
        'url' => env('FLASK_API_URL', 'http://localhost:5001'),
        'timeout' => env('FLASK_API_TIMEOUT', 600),
    ],

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'api_url' => env('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1/chat/completions'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'timeout' => env('DEEPSEEK_TIMEOUT', 60),
        'max_retries' => env('DEEPSEEK_MAX_RETRIES', 3),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],
];
