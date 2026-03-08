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

    'mykoob' => [
        'login_url' => env('MYKOOB_LOGIN_URL', 'https://login.mykoob.lv/#/lv/permissions'),
        'auth_url' => env('MYKOOB_AUTH_URL', 'https://www.mykoob.lv/?oauth2/authorizeDevice'),
        'resource_url' => env('MYKOOB_RESOURCE_URL', 'https://www.mykoob.lv/?api/resource'),
        'client' => env('MYKOOB_CLIENT', 'MykoobMobile'),
        'oauth' => [
            'authorize_url' => env('MYKOOB_OAUTH_AUTHORIZE_URL'),
            'token_url' => env('MYKOOB_OAUTH_TOKEN_URL'),
            'userinfo_url' => env('MYKOOB_OAUTH_USERINFO_URL'),
            'client_id' => env('MYKOOB_OAUTH_CLIENT_ID'),
            'client_secret' => env('MYKOOB_OAUTH_CLIENT_SECRET'),
            'redirect_uri' => env('MYKOOB_OAUTH_REDIRECT_URI'),
            'scopes' => env('MYKOOB_OAUTH_SCOPES', 'openid profile email'),
        ],
    ],

];
