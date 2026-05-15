<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | LDMS Secure Disk (encrypted at rest)
        |--------------------------------------------------------------------------
        |
        | Used by the LDMS module to store last-message attachments
        | (photos, voice recordings, video, PDFs, Word docs).
        |
        | The `encrypt => true` flag tells Laravel's filesystem adapter to
        | transparently AES-256-CBC encrypt the bytes of every file written
        | via Storage::disk('ldms_secure'), using your APP_KEY.  Reads
        | decrypt on the fly.  Files on disk are unreadable without the key.
        |
        | Requires Laravel 10.27+.
        */
        'ldms_secure' => [
            'driver'     => 'local',
            'root'       => storage_path('app/ldms-encrypted'),
            'encrypt'    => true,
            'visibility' => 'private',
            'throw'      => false,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
