<?php

return [
    'api' => [
        'version' => 'v1.0.0-beta',
        'url' => env('bkash_url')
    ],
    'credentials' => [
        'username' => env('bkash_username'),
        'password' => env('bkash_password'),
        'bkash_app_key' => env('bkash_app_key'),
        'bkash_app_secret' => env('bkash_app_secket')
    ]
];
