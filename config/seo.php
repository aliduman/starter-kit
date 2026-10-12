<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Indexing
    |--------------------------------------------------------------------------
    |
    | When false every page gets `noindex, nofollow` and robots.txt disallows
    | everything. Defaults to true only in production; set SEO_INDEXABLE=false
    | on test/staging hosts that run with APP_ENV=production.
    |
    */

    'indexable' => (bool) env('SEO_INDEXABLE', env('APP_ENV') === 'production'),

    /*
    |--------------------------------------------------------------------------
    | Trailing slash
    |--------------------------------------------------------------------------
    |
    | When true, public pages (module web routes and root content paths) are
    | served at URLs ending in "/" (e.g. to keep a previous site's indexed
    | URLs unchanged); when false, without it. The other form 301s to the
    | right one. See App\Support\Routing\TrailingSlash.
    |
    */

    'trailing_slash' => (bool) env('SEO_TRAILING_SLASH', false),

    /*
    |--------------------------------------------------------------------------
    | Title separator
    |--------------------------------------------------------------------------
    |
    | Page titles are "{title}{separator}{site name}". resources/js/app.js
    | builds client-side titles with the same rule.
    |
    */

    'title_separator' => env('SEO_TITLE_SEPARATOR', ' · '),

    /*
    |--------------------------------------------------------------------------
    | Fallbacks
    |--------------------------------------------------------------------------
    |
    | Used when neither the page nor a module's defaults resolver (see
    | App\Support\Seo\Seo::resolveDefaultsUsing) provides a value. `image` may
    | be relative to APP_URL.
    |
    */

    'description' => env('SEO_DESCRIPTION'),

    'image' => env('SEO_IMAGE'),

    'locale' => 'tr_TR',

    'twitter_site' => env('SEO_TWITTER_SITE'),

];
