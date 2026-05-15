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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'pakasir' => [
        'api_key' => env('PAKASIR_API_KEY'),
        'base_url' => env('PAKASIR_BASE_URL', 'https://app.pakasir.com'),
        'project' => env('PAKASIR_PROJECT', env('PAKASIR_SLUG')),
        'webhook_url' => env('PAKASIR_WEBHOOK_URL', env('PAKASIR_WEBHOOK_SECRET')),
        'payment_methods' => [
            'pakasir_qris',
            'pakasir_bni_va',
            'pakasir_bri_va',
            'pakasir_bnc_va',
            'pakasir_cimb_niaga_va',
            'pakasir_maybank_va',
            'pakasir_permata_va',
            'pakasir_atm_bersama_va',
            'pakasir_artha_graha_va',
            'pakasir_sampoerna_va',
        ],
    ],

    'evolution' => [
        'url' => env('EVOLUTION_API_URL'),
        'key' => env('EVOLUTION_API_KEY'),
        'global_key' => env('EVOLUTION_GLOBAL_API_KEY'),
        'default_instance' => env('EVOLUTION_DEFAULT_INSTANCE'),
    ],

];
