<?php

return [
    'facebook' => [
        'app_id' => env('FB_APP_ID'),
        'app_secret' => env('FB_APP_SECRET'),
    ],

    'accountkit' => [
        'app_id' => env('ACCOUNTKIT_APP_ID'),
        'app_secret' => env('ACCOUNTKIT_APP_SECRET'),
        'api_version' => env('ACCOUNTKIT_API_VERSION'),
    ],

    'accountkit_poco' => [
        'app_id' => env('ACCOUNTKITPOCO_APP_ID'),
        'app_secret' => env('ACCOUNTKITPOCO_APP_SECRET'),
        'api_version' => env('ACCOUNTKITPOCO_API_VERSION'),
    ],

    'E_KEY' => env('E_KEY'),
    'messenger' => [
        'api_token' => env('MESSENGER_API_TOKEN')
    ],
    'payment' => [
        'portwallet_url' => env('PORTWALLET_URL'),
        'portwallet_payment_url' => env('PORTWALLET_PAYMENT_URL'),
        'portwallet_key' => env('PORTWALLET_APP_KEY'),
        'portwallet_secret' => env('PORTWALLET_SECRET_KEY')
    ],

    'APP_ENV' => env('APP_ENV'),
    'APP_API_AUTH_KEY' => env('APP_API_AUTH_KEY')
];
