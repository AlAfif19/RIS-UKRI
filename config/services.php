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

    /*
    |--------------------------------------------------------------------------
    | Master Data API UKRI
    |--------------------------------------------------------------------------
    |
    | Kredensial untuk https://api.ukri.web.id/api/v1 - API read-only yang
    | menyediakan data fakultas, prodi, angkatan, peminatan, dosen, dan
    | mahasiswa. Lihat App\Services\UkriMasterDataService.
    |
    */

    'ukri' => [
        'base_url' => env('UKRI_API_BASE_URL', 'https://api.ukri.web.id/api/v1'),
        'token' => env('UKRI_API_TOKEN'),
        // Berapa lama (menit) respons fakultas/prodi disimpan di cache aplikasi,
        // di luar mirror tabel lokal yang dibuat oleh perintah ukri:sync.
        'cache_ttl' => env('UKRI_API_CACHE_TTL', 1440),
    ],

];
