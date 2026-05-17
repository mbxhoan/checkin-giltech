<?php

return [
    'disk' => env('REGISTRATION_FILE_DISK', 'local'),

    // Keep under a non-public prefix on the selected disk.
    'root_prefix' => env('REGISTRATION_FILE_ROOT_PREFIX', 'private/registration-files'),

    'max_size_kb' => (int) env('REGISTRATION_FILE_MAX_SIZE_KB', 2048),

    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],

    'allowed_mime_types' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ],

    'temporary_ttl_hours' => (int) env('REGISTRATION_FILE_TEMP_TTL_HOURS', 24),

    'cleanup_enabled' => env('REGISTRATION_FILE_CLEANUP_ENABLED', true),
];
