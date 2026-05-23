<?php

return [

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'iyzico' => [
        'api_key' => env('IYZICO_API_KEY'),
        'secret_key' => env('IYZICO_SECRET_KEY'),
        'base_url' => env('IYZICO_BASE_URL', 'https://sandbox-api.iyzipay.com'),
    ],

];
