<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => 'key-5bf2add7de1d5185e1f36787b20bbc58',
        'secret' => 'sandboxd293eacb6dc048659fdf7feab42a6888.mailgun.org',
    ],

    'mandrill' => [
        'secret' => '',
    ],

    'ses' => [
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model' => 'User',
        'secret' => '',
    ],

    'gcm' => [
        'key' => env('GCM_KEY'),
        'sender_id' => env('GCM_SENDER_ID'),
    ],

    'facebook' => [
        'client_id' => env('FB_Client_ID'),
        'client_secret' => env('FB_Client_SECRET'),
        'redirect' => env('FB_REDIRECT_URL')
    ]

];
