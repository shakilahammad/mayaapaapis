<?php

return [
    'api' => [
        'port' => env('gp_port'),
        'url' => env('gp_url')
    ],
    'credentials' => [
        'client_id' => env('gp_client_id'),
        'client_secret' => env('gp_client_secret'),
        'charge_code' => env('gp_charge_code'),
        'serviceId' => env('gp_serviceId'),
        'idType' => env('gp_idType'),
        'category' => env('gp_category'),
        'grant_type' => env('gp_grant_type'),
        'sourceId' => env('gp_sourceId')
    ]
];
