<?php

return [
    'allow_debug_routes' => env('API_ALLOW_DEBUG_ROUTES', false),

    'client_basic_auth' => [
        'users' => env('CLIENT_AUTH_USERS', 'apiclient,apitest'),
        'password' => env('CLIENT_AUTH_PASS', env('REGISTER_AUTH_PASS')),
    ],

    'rate_limits' => [
        // Default API throughput: keep this high enough for normal traffic bursts.
        'api_per_minute' => env('API_RATE_LIMIT_API_PER_MINUTE', 600),
        'api_per_second' => env('API_RATE_LIMIT_API_PER_SECOND', 40),

        // Registration and order mutation endpoints.
        'registration_per_minute' => env('API_RATE_LIMIT_REGISTRATION_PER_MINUTE', 120),
        'registration_per_second' => env('API_RATE_LIMIT_REGISTRATION_PER_SECOND', 8),
        'registration_upload_per_minute' => env('API_RATE_LIMIT_REGISTRATION_UPLOAD_PER_MINUTE', 40),
        'registration_upload_per_second' => env('API_RATE_LIMIT_REGISTRATION_UPLOAD_PER_SECOND', 4),

        // Portal login must stay stricter to reduce brute-force/spam attempts.
        'portal_login_per_minute' => env('API_RATE_LIMIT_PORTAL_LOGIN_PER_MINUTE', 20),
        'portal_login_per_second' => env('API_RATE_LIMIT_PORTAL_LOGIN_PER_SECOND', 10),

        // Legacy client sync endpoint (v1/clients/upsert-by-id).
        'client_upsert_per_minute' => env('API_RATE_LIMIT_CLIENT_UPSERT_PER_MINUTE', 180),
        'client_upsert_per_second' => env('API_RATE_LIMIT_CLIENT_UPSERT_PER_SECOND', 15),

        // OnePay callbacks should be permissive enough for provider retries.
        'onepay_callback_per_minute' => env('API_RATE_LIMIT_ONEPAY_CALLBACK_PER_MINUTE', 360),
        'onepay_callback_per_second' => env('API_RATE_LIMIT_ONEPAY_CALLBACK_PER_SECOND', 30),
        'onepay_querydr_per_minute' => env('API_RATE_LIMIT_ONEPAY_QUERYDR_PER_MINUTE', 60),
        'onepay_querydr_per_second' => env('API_RATE_LIMIT_ONEPAY_QUERYDR_PER_SECOND', 8),

        // Inbound webhooks from external providers.
        'webhook_per_minute' => env('API_RATE_LIMIT_WEBHOOK_PER_MINUTE', 120),
        'webhook_per_second' => env('API_RATE_LIMIT_WEBHOOK_PER_SECOND', 10),
    ],

    // Optional emergency circuit breaker by remote IP for all /api/* requests.
    'global_ip_throttle' => [
        'enabled' => env('API_GLOBAL_IP_THROTTLE_ENABLED', false),
        'per_minute' => env('API_GLOBAL_IP_THROTTLE_PER_MINUTE', 2000),
    ],

    // Token issued by /api/portal/login for profile/file actions.
    'portal_login_token_ttl_minutes' => env('API_PORTAL_LOGIN_TOKEN_TTL_MINUTES', 1440),
];
