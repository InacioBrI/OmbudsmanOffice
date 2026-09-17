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
    | Integração com a RA API (Reclame AQUI).
    | Enquanto as credenciais oficiais não chegam, 'enabled' deve ficar false:
    | o container resolve o NullReclameAquiClient e nada é chamado externamente.
    | TODO(reclame-aqui): ajustar base_url/endpoints e implementar OAuth2 real
    | (client_id/client_secret) quando a documentação estiver disponível.
    */
    'reclame_aqui' => [
        'enabled' => env('RECLAME_AQUI_ENABLED', false),
        'base_url' => env('RECLAME_AQUI_BASE_URL'),
        'token' => env('RECLAME_AQUI_TOKEN'),
        'client_id' => env('RECLAME_AQUI_CLIENT_ID'),
        'client_secret' => env('RECLAME_AQUI_CLIENT_SECRET'),
        'timeout' => env('RECLAME_AQUI_TIMEOUT', 15),
        'connect_timeout' => env('RECLAME_AQUI_CONNECT_TIMEOUT', 5),
        'retry' => env('RECLAME_AQUI_RETRY', 2),
    ],

];
