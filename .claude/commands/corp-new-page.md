---
description: Corporate modülüne yeni kurumsal sayfa (Page) ekler — migration, model, Filament resource, route, Vue page ve test.
---

`corporate-cms` skill'ini takip ederek Corporate modülüne yeni bir Page ekle.

Kullanıcı girdisi: $ARGUMENTS

Beklenen sırada ilerle:
1. Migration: `corporate_<plural>` tablosu — slug, title, body, excerpt, is_published, published_at, seo JSON
2. Model: `app/Modules/Corporate/Models/<Name>.php` — `protected $table = 'corporate_<plural>';`, HasSlug + InteractsWithMedia trait'leri
3. Filament Resource: `Pages` alt-klasörü ile birlikte; `use App\Modules\Corporate\Filament\Resources\<Name>Resource\Pages;` import'unu unutma
4. Route + Controller: `app/Modules/Corporate/Routes/web.php` ve `Http/Controllers/<Name>Controller.php`
5. Vue page: `resources/js/Pages/Corporate/<Name>.vue` — AppLayout ile sarmalı
6. Pest test: `tests/Feature/Corporate/<Name>Test.php` — create + show published + hide unpublished

Tamamladığında: `php artisan migrate`, `php artisan test --filter=<Name>Test`, sonra `curl -I http://127.0.0.1:8001/admin/<plural>` ile 200/302 doğrulaması yap.

Şüphe varsa `corporate-architect` agent'ını çağır.
