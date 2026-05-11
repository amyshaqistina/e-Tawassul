<?php

return [
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

    /*
     * iMaalum scraper config — used by ImaalumScraperService.
     */
    'imaalum' => [
        'api_base'             => env('IMAALUM_API_BASE', 'https://api.quddus.my/api'),
        'directory_url'        => env('IMAALUM_DIRECTORY_URL', 'https://www.iium.edu.my/directory/'),
        'directory_fallback'   => env('LECTURER_DIRECTORY_FALLBACK', false),
        'cache_minutes'        => env('IMAALUM_CACHE_MINUTES', 30),
    ],
];
