<?php

return [
    'name' => env('APP_NAME', 'Starter Kit'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'tr'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'tr_TR'),
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),

    // /kurulum/{token} (SetupController) yalnızca doluyken açılır. Kullanmadığınız dönemde boş bırakın.
    'setup_token' => env('SETUP_TOKEN'),

    // İlk kurulumda seed'in açtığı admin (DatabaseSeeder). Production'da boşsa admin oluşturulmaz.
    'setup_admin' => [
        'email' => env('SETUP_ADMIN_EMAIL'),
        'password' => env('SETUP_ADMIN_PASSWORD'),
    ],

    // /kurulum/{token}?seeders=ad1,ad2 ile çalıştırılabilen ek seeder'lar (ad => sınıf), ör. örnek içerik.
    // Yalnızca production dışı ortamlarda çalışır.
    'setup_seeders' => [],

    // /kurulum'un altında uyardığı yamalı PHP sürümleri, dal => en düşük sürüm (App\Support\PhpVersionCheck). PHP bir
    // güvenlik sürümü çıkardığında güncelleyin. 8.4.27 / 8.5.12: PHP2Shell (CVE-2026-109618, CVE-2026-109619).
    'php_security_minimums' => [
        '8.4' => '8.4.27',
        '8.5' => '8.5.12',
    ],

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];
