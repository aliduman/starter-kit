<?php

use Spatie\ResponseCache\CacheItemSelector\DefaultCacheItemSelector;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Spatie\ResponseCache\Hasher\DefaultHasher;
use Spatie\ResponseCache\Replacers\CsrfTokenReplacer;
use Spatie\ResponseCache\Serializers\DefaultSerializer;

return [
    'enabled' => env('RESPONSE_CACHE_ENABLED', true),
    'cache_profile' => CacheAllSuccessfulGetRequests::class,
    'cache_lifetime_in_seconds' => env('RESPONSE_CACHE_LIFETIME', 60 * 60 * 24 * 7),
    'add_cache_time_header' => env('APP_DEBUG', false),
    'cache_time_header_name' => 'X-Cached-At',
    'cache_store' => env('RESPONSE_CACHE_DRIVER', 'database'),
    'replacers' => [
        CsrfTokenReplacer::class,
    ],
    'hasher' => DefaultHasher::class,
    'serializer' => DefaultSerializer::class,
    'cache_item_selector' => DefaultCacheItemSelector::class,
];
