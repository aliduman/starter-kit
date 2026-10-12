<?php

/*
 * Ziggy: named routes for the frontend (`route()` in resources/js/route.js). Admin and internal routes are left
 * out of the page payload. `middleware` exposes only the TrailingSlash marker, and only while the rule is on, so the
 * JS helper builds the same URLs as the server.
 */
return [
    'except' => ['filament.*', 'livewire.*', 'sanctum.*', 'storage.*', 'ignition.*', 'boost.*'],

    'middleware' => env('SEO_TRAILING_SLASH', false) ? [App\Support\Routing\TrailingSlash::MIDDLEWARE] : [],
];
