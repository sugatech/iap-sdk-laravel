<?php

return [
    'api_url' => env('IAP_API_URL'),
    'oauth' => [
        'url' => env('IAP_OAUTH_URL', env('IAP_API_URL') . '/oauth/token'),
        'client_id' => env('IAP_OAUTH_CLIENT_ID'),
        'client_secret' => env('IAP_OAUTH_CLIENT_SECRET'),
    ],
];
