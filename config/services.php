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

    'discord' => [
        'client_id' => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'redirect' => env('DISCORD_REDIRECT_URI'),

        // optional
        'allow_gif_avatars' => (bool) env('DISCORD_AVATAR_GIF', true),
        'avatar_default_extension' => env('DISCORD_EXTENSION_DEFAULT', 'png'), // only pick from jpg, png, webp

        // Discord API configuration
        'api' => [
            'base_url' => env('DISCORD_API_BASE_URL', 'https://discord.com/api/v10'),
            'bot_token' => env('DISCORD_BOT_TOKEN'),
            'guild_id' => env('DISCORD_GUILD_ID'), // Optional global guild ID
            'timeout' => env('DISCORD_API_TIMEOUT', 30), // seconds
            'retry_attempts' => env('DISCORD_API_RETRY_ATTEMPTS', 3),
            'retry_delay' => env('DISCORD_API_RETRY_DELAY', 1000), // milliseconds
        ],
    ],

    'gamebridge' => [
        'timeout' => env('GAMEBRIDGE_TIMEOUT', 5), // seconds
        'retry_attempts' => env('GAMEBRIDGE_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('GAMEBRIDGE_RETRY_DELAY', 1000), // milliseconds
        'default_cache_time' => env('GAMEBRIDGE_DEFAULT_CACHE_TIME', 30), // seconds
    ],

    'bab' => [
        'base_url' => env('BAB_BASE_URL'),
        'client_id' => env('BAB_CLIENT_ID'),
        'client_secret' => env('BAB_CLIENT_SECRET'),
        'redirect' => env('BAB_REDIRECT_URI'),
    ],
];
