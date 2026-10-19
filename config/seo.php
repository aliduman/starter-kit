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
    | Robots directives
    |--------------------------------------------------------------------------
    |
    | The robots meta of indexable pages. `max-image-preview:large` lets search
    | engines show large image previews (Google Discover needs it); WordPress
    | (Yoast) sends the same directives.
    |
    */

    'robots' => env('SEO_ROBOTS', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'),

    /*
    |--------------------------------------------------------------------------
    | Single address
    |--------------------------------------------------------------------------
    |
    | When true, GET requests arriving with another scheme or host than APP_URL
    | (http://, www., an old test subdomain) get a 301 to the same path on
    | APP_URL, so search engines never index duplicate copies. Follows
    | `indexable` by default: test and local hosts run under other addresses.
    |
    */

    'redirect_to_app_url' => (bool) env('SEO_REDIRECT_TO_APP_URL', env('SEO_INDEXABLE', env('APP_ENV') === 'production')),

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
    | Page titles are "{title}{separator}{site name}" (App\Support\Seo\Seo).
    | Titles that already contain the site name are used as they are.
    |
    */

    'title_separator' => env('SEO_TITLE_SEPARATOR', ' · '),

    /*
    |--------------------------------------------------------------------------
    | Fallbacks
    |--------------------------------------------------------------------------
    |
    | Used when neither the page nor a module's defaults resolver (see
    | App\Support\Seo\Seo::resolveDefaultsUsing) provides a value. `home_title`
    | and `description` apply to the homepage only. `image` may be relative to
    | APP_URL.
    |
    */

    'home_title' => env('SEO_HOME_TITLE'),

    'description' => env('SEO_DESCRIPTION'),

    'image' => env('SEO_IMAGE'),

    'locale' => 'tr_TR',

    'twitter_site' => env('SEO_TWITTER_SITE'),

    /*
    |--------------------------------------------------------------------------
    | Verification and analytics
    |--------------------------------------------------------------------------
    |
    | Search engine verification meta tags (meta name => token) and analytics
    | trackers (provider => id; loaded in the browser only after cookie
    | consent, and only when `indexable`). A site's settings panel can
    | override both through Seo::resolveDefaultsUsing.
    |
    */

    'verification' => [
        'google-site-verification' => env('SEO_GOOGLE_SITE_VERIFICATION'),
        'msvalidate.01' => env('SEO_BING_SITE_VERIFICATION'),
        'yandex-verification' => env('SEO_YANDEX_VERIFICATION'),
    ],

    'analytics' => [
        'ga4' => env('SEO_GA4_ID'),
    ],

];
