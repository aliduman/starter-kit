---
name: corporate-cms
description: Corporate (kurumsal) projesinde yeni varlık (Page, Section, Menu, ContactForm, TeamMember vb.) eklemek için adım adım rehber. Migration → Model → Filament Resource → Route → Controller → Vue Page → Pest test.
---

# Corporate CMS — Feature Ekleme Rehberi

Bu skill `app/Modules/Corporate` modülünde yeni bir varlık veya feature eklerken takip edilecek 6 adımı tanımlar. Domain bilgisi için `corporate-architect` agent'ı çağır.

## Adım 1: Domain analizi

Kullanıcının istediği varlık nedir? Karar ağacı:

- **Statik içerikli bir sayfa mı?** (Hakkımızda, Misyon) → `Page` varyantı, Rich Editor ile body
- **Bir sayfanın bölümü mü?** (Hero, Feature list, Testimonial) → `Section` (polymorphic veya page_id FK)
- **Menü öğesi mi?** → `MenuItem` (parent_id ile hiyerarşik)
- **Form submission mı?** (İletişim, başvuru) → `ContactForm` / `Application` tablosu + e-posta notification
- **Ekip/kişi mi?** → `TeamMember` (photo MediaLibrary ile)
- **Singleton ayar mı?** (Şirket bilgileri, sosyal media linkleri) → tek-row tablo (`corporate_settings`) ve Filament `Page` (Resource değil)

## Adım 2: Migration

- **Path**: `app/Modules/Corporate/Database/migrations/YYYY_MM_DD_HHmmss_create_<name>_table.php`
- **Tablo adı**: `corporate_<plural>` — örn `corporate_pages`, `corporate_team_members`
- **Standart kolonlar** (her varlık için):
  - `id` (bigIncrements)
  - `slug` (string, unique) — sayfa-benzeri varlıklar için
  - `title` (string) veya `name` (string)
  - `is_published` (boolean, default false, indexed)
  - `published_at` (timestamp, nullable)
  - `timestamps()`
- **SEO kolonu**: Page-benzeri için `seo` JSON kolonu — `{title, description, og_image, canonical}`
- **MediaLibrary**: Görsel ekleyeceksen ayrı kolon GEREKMİYOR; Spatie MediaLibrary `media` tablosunu kullanır.

Migration sonrası: `php artisan migrate` (MAMP'da DB_PORT=8889).

## Adım 3: Eloquent Model

- **Path**: `app/Modules/Corporate/Models/<Name>.php`
- **`$table` ataması ZORUNLU**: `protected $table = 'corporate_<plural>';` — yoksa Laravel `<plural>` arar ve `Table not found` patlar (bu hatayı bir kez yaşadık, tekrarlama).
- **Trait'ler** (gerektiğinde):
  - `HasSlug` (Spatie) — slug otomatik üretim
  - `InteractsWithMedia` + `implements HasMedia` (Spatie MediaLibrary) — görsel ekleme
  - `HasFactory` — test data için
- **`$fillable`**: tüm formdan gelen alanlar
- **`$casts`**: `is_published => boolean`, `published_at => datetime`, `seo => array`
- **`getSlugOptions()`**: HasSlug için `generateSlugsFrom('title')->saveSlugsTo('slug')`

## Adım 4: Filament Resource

- **Path**: `app/Modules/Corporate/Filament/Resources/<Name>Resource.php`
- **Sub-folder**: `Resources/<Name>Resource/Pages/` içine `ListXxx.php`, `CreateXxx.php`, `EditXxx.php`
- **KRİTİK import**: Resource dosyasının üstüne `use App\Modules\Corporate\Filament\Resources\<Name>Resource\Pages;` ekle — yoksa `Pages\ListXxx::route('/')` resolve etmez (history'de bu unutulmuştu).
- **Navigation**: `protected static ?string $navigationGroup = 'Corporate';`
- **Form fields**: TextInput (title/slug), RichEditor (body), Toggle (is_published), DateTimePicker (published_at), Fieldset (SEO ile nested)
- **Table columns**: title, slug, is_published badge, published_at, updated_at
- **Filters**: published filter (TernaryFilter)

## Adım 5: Public Route + Controller + Vue Page

- **Route**: `app/Modules/Corporate/Routes/web.php` altına ekle:
  ```php
  Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');
  ```
- **Controller**: `app/Modules/Corporate/Http/Controllers/<Name>Controller.php` — Inertia::render
- **Vue page**: `resources/js/Pages/Corporate/<Name>.vue` — AppLayout ile sarmalı
- Response cache eklemek isterseniz Controller'da `cacheResponse:600` middleware ekle (10dk)

## Adım 6: Pest Test

- **Path**: `tests/Feature/Corporate/<Name>Test.php`
- En az:
  - `it creates a <name>` — admin login, post resource create endpoint, assert DB
  - `it shows published <name> on public route`
  - `it hides unpublished <name>`
  - `it returns 404 for non-existent slug`

`php artisan test --filter=<Name>Test` ile çalıştır.

## Doğrulama

1. `php artisan migrate:status` — yeni migration `[Ran]` görünmeli
2. `http://127.0.0.1:8001/admin/<plural>` — Filament listede açılır
3. Bir kayıt oluştur, publish toggle aç
4. `http://127.0.0.1:8001/<slug>` — Vue page render olur (200 + içerik)
5. `php artisan test` — tüm testler yeşil

## Anti-Pattern'ler (KAÇIN)

- Page body'sini ham HTML olarak Rich Editor olmadan saklamak (XSS)
- Slug'ı manuel input bırakmak — HasSlug otomatik üretsin
- SEO için ayrı tablo açmak — JSON kolon yeterli ve Filament'te tek fieldset ile yönetilir
- İletişim formunda CSRF token eksikliği veya rate limit yokluğu
- `$table` ataması unutmak (en sık hata)

## MAMP Lokal Notları

- DB_PORT=8889, DB_USERNAME=root, DB_PASSWORD=root
- Server: `php artisan serve --port=8001`
- Vite: `npm run dev` (port 5173)
- Local Redis yok → SESSION/CACHE/QUEUE database driver kullanıyor
- Filament asset publish: `composer install` post-hook'u otomatik çalıştırıyor; manuel: `php artisan filament:assets`

## Production Cutover Hatırlatma

Production'a çıkarken Redis ayağa kaldır ve şu dördünü `redis`'e çevir:
1. `.env` → `SESSION_DRIVER=redis`
2. `.env` → `CACHE_STORE=redis`
3. `.env` → `QUEUE_CONNECTION=redis`
4. `config/responsecache.php` → `cache_store` `'redis'`

Ek olarak: `php artisan config:cache route:cache view:cache event:cache`.
