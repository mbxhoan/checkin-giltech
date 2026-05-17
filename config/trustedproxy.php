<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trusted Proxy Addresses
    |--------------------------------------------------------------------------
    |
    | Comma-separated list or "*" for all proxies. Keep this explicit in
    | production to prevent spoofed X-Forwarded-For headers.
    |
    */
    'proxies' => env('TRUSTED_PROXIES'),
];
