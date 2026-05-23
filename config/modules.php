<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Registry
    |--------------------------------------------------------------------------
    |
    | Each entry registers an independently toggleable module that lives under
    | app/Modules/{Namespace}. Disable a module by flipping `enabled` to false
    | (or setting the matching .env flag) and its routes, views, migrations
    | and Filament resources will no longer be loaded.
    |
    */

    'list' => [

        'corporate' => [
            'enabled' => env('MODULE_CORPORATE_ENABLED', true),
            'namespace' => 'Corporate',
            'name' => 'Corporate',
            'description' => 'Kurumsal sayfalar, hakkımızda, iletişim.',
            'prefix' => null,
            'provider' => App\Modules\Corporate\Providers\CorporateServiceProvider::class,
        ],

        'ecommerce' => [
            'enabled' => env('MODULE_ECOMMERCE_ENABLED', true),
            'namespace' => 'Ecommerce',
            'name' => 'E-Commerce',
            'description' => 'Ürün kataloğu, sepet, ödeme (Stripe + iyzico).',
            'prefix' => 'shop',
            'default_gateway' => env('ECOMMERCE_DEFAULT_GATEWAY', 'stripe'),
            'provider' => App\Modules\Ecommerce\Providers\EcommerceServiceProvider::class,
        ],

        'realestate' => [
            'enabled' => env('MODULE_REALESTATE_ENABLED', true),
            'namespace' => 'RealEstate',
            'name' => 'Real Estate',
            'description' => 'Emlak ilanları, arama, filtreleme.',
            'prefix' => 'emlak',
            'provider' => App\Modules\RealEstate\Providers\RealEstateServiceProvider::class,
        ],

        'media' => [
            'enabled' => env('MODULE_MEDIA_ENABLED', true),
            'namespace' => 'Media',
            'name' => 'Media',
            'description' => 'Haber, makale, kategori, etiket.',
            'prefix' => 'haber',
            'provider' => App\Modules\Media\Providers\MediaServiceProvider::class,
        ],

    ],

];
