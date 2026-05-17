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
        'request_timeout_seconds' => env('POSTMARK_REQUEST_TIMEOUT_SECONDS', 120),
        'connect_timeout_seconds' => env('POSTMARK_CONNECT_TIMEOUT_SECONDS', 10),
        'retry_times' => env('POSTMARK_RETRY_TIMES', 3),
        'retry_sleep_milliseconds' => env('POSTMARK_RETRY_SLEEP_MILLISECONDS', 1500),
        'max_attachment_bytes' => env('POSTMARK_MAX_ATTACHMENT_BYTES', 7340032),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
