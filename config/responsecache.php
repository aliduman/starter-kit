<?php

// Only overridden keys live here; the package config fills the remaining
// top-level keys (bypass, ignored_query_parameters, hasher, serializer, replacers).
// Nested arrays are merged shallowly, so each overridden group must be complete.
return [

    'enabled' => env('RESPONSE_CACHE_ENABLED', true),

    'cache' => [
        'store' => env('RESPONSE_CACHE_DRIVER', 'file'),
        // env() string döndürür; Carbon 3 int ister — cast şart
        'lifetime_in_seconds' => (int) env('RESPONSE_CACHE_LIFETIME', 60 * 60 * 24 * 7),
        'tag' => env('RESPONSE_CACHE_TAG', ''),
    ],

    'debug' => [
        'enabled' => env('APP_DEBUG', false),
        'cache_time_header_name' => 'X-Cache-Time',
        'cache_status_header_name' => 'X-Cache-Status',
        'cache_age_header_name' => 'X-Cache-Age',
        'cache_key_header_name' => 'X-Cache-Key',
    ],

    /*
     * Inertia uyumlu profil: HTML ve Inertia XHR istekleri
     * ayrı cache anahtarlarında tutulur.
     */
    'cache_profile' => App\Support\ResponseCache\InertiaAwareCacheProfile::class,

];
