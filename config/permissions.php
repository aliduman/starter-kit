<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Panel izinleri
    |--------------------------------------------------------------------------
    |
    | Çekirdek alanların izinleri. Her modülün izni (config/modules.php `permission`, varsayılan manage-{anahtar})
    | seeder'da eklenir; modülün panel kaynakları o izni ister (App\Filament\Concerns\ChecksPermission).
    |
    */

    'permissions' => [
        'manage-site',      // site ayarları, menüler, ana sayfa bölümleri
        'manage-content',   // kategoriler, etiketler, kişiler
        'manage-users',
        'view-activity',    // işlem geçmişi
    ],

    /*
    |--------------------------------------------------------------------------
    | Roller
    |--------------------------------------------------------------------------
    |
    | Rol => izinler ('*' tüm izinler). Panele yalnızca bu rollerden biri girer. Site kendi rolünü burada tanımlar;
    | tanımsız izin (kapalı ya da olmayan modül) atlanır.
    |
    */

    'roles' => [
        'super-admin' => '*',
        'admin' => [
            'manage-site',
            'manage-content',
            'view-activity',
            'manage-corporate',
            'manage-ecommerce',
            'manage-realestate',
            'manage-media',
            'manage-announcements',
        ],
        'editor' => [
            'manage-content',
            'manage-corporate',
            'manage-media',
            'manage-announcements',
        ],
    ],

];
