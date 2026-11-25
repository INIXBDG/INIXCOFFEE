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

    'forumium' => [
        'url' => env('FORUMIUM_URL'),
        'secret' => env('FORUMIUM_SSO_SECRET'),
    ],

    'vapid' => [
        'public_key'  => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject'     => env('VAPID_SUBJECT', 'mailto:support@inixindobdg.co.id'),
    ],

    'telegram_pickup_driver' => [
        'bot_token' => env('TELEGRAM_PICKUP_BOT_TOKEN'),
        'chat_id'   => env('TELEGRAM_PICKUP_CHAT_ID'),
    ],

    'telegram' => [
        'bot_token'      => env('TELEGRAM_BOT_TOKEN'),
        'group_id'       => env('TELEGRAM_GROUP_ID'),
        'webhook_url'    => env('TELEGRAM_WEBHOOK_URL'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],

    'fonnte' => [
        'url'          => env('FONNTE_URL', 'https://api.fonnte.com/send'),
        'api_key'      => env('FONNTE_API_KEY'),
        'api_token'    => env('FONNTE_API_TOKEN', env('FONNTE_API_KEY')),
        'target_group' => env('FONNTE_TARGET_GROUP', '120363418574215044@g.us'),
    ],

    'moodle' => [
        'url'          => env('MOODLE_URL'),
        'token'        => env('MOODLE_TOKEN'),
        'api_url'      => env('MOODLE_API_URL'),
        'api_username' => env('MOODLE_API_USERNAME'),
        'api_password' => env('MOODLE_API_PASSWORD'),
    ],

    'internal_api' => [
        'username'     => env('API_USERNAME'),
        'password'     => env('API_PASSWORD'),
        'ticket_token' => env('INTERNAL_TICKET_TOKEN'),
    ],

    'external_web' => [
        'webhook_token' => env('EXTERNAL_WEB_WEBHOOK_TOKEN'),
    ],
];
