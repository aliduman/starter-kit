<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Registry
    |--------------------------------------------------------------------------
    |
    | Each entry registers an independently toggleable module that lives under
    | app/Modules/{namespace}. Disable a module by flipping `enabled` to false
    | (or setting the matching .env flag) and its provider, routes, views,
    | migrations, console commands and Filament resources will not be loaded.
    |
    | `prefix` is the URL prefix for the module's web routes; null/'' mounts
    | them at the site root. API routes are mounted under /api/{prefix|key}.
    |
    */

    'list' => [

        'corporate' => [
            'enabled' => env('MODULE_CORPORATE_ENABLED', true),
            'namespace' => 'Corporate',
            'name' => 'Corporate',
            'description' => 'Kurumsal sayfalar, hakkımızda, iletişim.',
            'prefix' => env('MODULE_CORPORATE_PREFIX', ''),
            'provider' => App\Modules\Corporate\Providers\CorporateServiceProvider::class,
        ],

        'ecommerce' => [
            'enabled' => env('MODULE_ECOMMERCE_ENABLED', true),
            'namespace' => 'Ecommerce',
            'name' => 'E-Commerce',
            'description' => 'Ürün kataloğu, sepet, ödeme (Stripe + iyzico).',
            'prefix' => env('MODULE_ECOMMERCE_PREFIX', 'shop'),
            'default_gateway' => env('ECOMMERCE_DEFAULT_GATEWAY', 'stripe'),
            'provider' => App\Modules\Ecommerce\Providers\EcommerceServiceProvider::class,
        ],

        'realestate' => [
            'enabled' => env('MODULE_REALESTATE_ENABLED', true),
            'namespace' => 'RealEstate',
            'name' => 'Real Estate',
            'description' => 'Emlak ilanları, arama, filtreleme.',
            'prefix' => env('MODULE_REALESTATE_PREFIX', 'emlak'),
            'provider' => App\Modules\RealEstate\Providers\RealEstateServiceProvider::class,
        ],

        'media' => [
            'enabled' => env('MODULE_MEDIA_ENABLED', true),
            'namespace' => 'Media',
            'name' => 'Media',
            'description' => 'Haber, makale, kategori, etiket.',
            'prefix' => env('MODULE_MEDIA_PREFIX', 'haber'),
            'provider' => App\Modules\Media\Providers\MediaServiceProvider::class,
        ],

    ],

];
