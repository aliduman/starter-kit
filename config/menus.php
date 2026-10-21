<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menü konumları
    |--------------------------------------------------------------------------
    |
    | Tema bu anahtarları paylaşılan `menus` prop'undan okur (App\Models\MenuItem::forFrontend). Site yeni bir
    | konum (ör. üst şerit) eklerse buraya yazar.
    |
    */

    'locations' => [
        'main' => 'Ana Menü',
        'footer' => 'Alt Menü',
    ],

    /*
    |--------------------------------------------------------------------------
    | İlk kurulum öğeleri (db:seed)
    |--------------------------------------------------------------------------
    |
    | Aynı başlıklı öğe varsa dokunulmaz; `route` verilen öğe rotası yoksa (modül kapalı) atlanır.
    |
    */

    'defaults' => [
        'main' => [
            ['title' => 'Anasayfa', 'url' => '/'],
            ['title' => 'Hakkımızda', 'route' => 'corporate.about'],
            ['title' => 'Mağaza', 'route' => 'ecommerce.products.index'],
            ['title' => 'Emlak', 'route' => 'realestate.listings.index'],
            ['title' => 'Haberler', 'route' => 'media.articles.index'],
            ['title' => 'Duyurular', 'route' => 'announcements.index'],
        ],
        'footer' => [
            ['title' => 'İletişim', 'route' => 'corporate.contact'],
        ],
    ],

];
