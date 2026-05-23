---
name: realestate-cms
description: RealEstate (emlak) projesinde yeni varlık (Listing, City, District, Category, Feature, Filter) eklemek için adım adım rehber. Map marker, m²/fiyat/oda filtreleme, galeri ordering dahil.
---

# RealEstate CMS — Feature Ekleme Rehberi

`app/Modules/RealEstate` modülünde feature ekleme. Domain için `realestate-architect` agent'ı çağır. Arama/filtreleme yoğun bir modül — query performansına dikkat.

## Adım 1: Domain analizi

Karar ağacı:
- **Yeni ilan tipi mi?** (devren satılık vb.) → `ListingType` enum'a ekle veya tablo varsa kayıt ekle
- **Kategori mi?** (daire/villa/arsa) → `Category` (hiyerarşik)
- **Lokasyon mu?** (yeni il/ilçe) → seed dosyasına ekle, migration GEREK YOK
- **Yeni filtre alanı mı?** → Listing migration'a kolon ekle + `ListingFilter` query builder'a method ekle + frontend filtre UI
- **Yeni özellik (feature) mi?** (asansör, otopark) → `Feature` tablosuna kayıt + Listing'e pivot
- **Galeri özelliği mi?** (panoramic, video) → Listing model'inde yeni MediaLibrary collection

## Adım 2: Migration

- **Path**: `app/Modules/RealEstate/Database/migrations/YYYY_MM_DD_HHmmss_*.php`
- **Tablo adı**: `realestate_<plural>` — `realestate_listings`, `realestate_cities`
- **Önemli kolon tipleri**:
  - `price` → `decimal(12, 2)` (TL/USD/EUR)
  - `currency` → `string(3)` (varsayılan 'TRY')
  - `area_m2` → `decimal(8, 2)` — brüt; `net_area_m2` ayrı kolon olsun
  - `latitude`, `longitude` → `decimal(10, 7)` — **ASLA varchar kullanma**
  - `rooms`, `bathrooms` → integer (Türkiye notation için `rooms_layout` string `'3+1'`)
  - `building_age` → integer (yıl)
  - `floor`, `total_floors` → integer
  - `expires_at` → timestamp (her ilanın süresi olmalı)
- **Index'ler**:
  - composite: `(city_id, district_id, is_published, expires_at)`
  - `price` (range query için)
  - `area_m2` (range query için)
  - lat/lng için spatial index (MySQL `POINT` veya iki ayrı index)

## Adım 3: Eloquent Model

- **Path**: `app/Modules/RealEstate/Models/<Name>.php`
- **`$table = 'realestate_<plural>';`** ZORUNLU
- **Trait'ler**:
  - `HasSlug` — slug otomatik (format: `{city}-{district}-{type}-{rooms}-{id}`)
  - `InteractsWithMedia` — galeri için (`'gallery'`, `'cover'` collections)
- **$casts**:
  - `price`, `area_m2`, `latitude`, `longitude` → `decimal:2` / `decimal:7`
  - `is_published`, `is_featured`, `is_furnished` → boolean
  - `expires_at`, `published_at` → datetime
  - `features` → array (eğer JSON kolonsa)
- **Scope'lar**:
  - `published()` — `is_published=true AND expires_at > now()`
  - `byCity($id)`, `byDistrict($id)`
  - `priceRange($min, $max)`
  - `areaRange($min, $max)`
- **Relations**: belongsTo City, District, Category, User (owner/agent); belongsToMany Feature

## Adım 4: Filament Resource

- **Path**: `app/Modules/RealEstate/Filament/Resources/<Name>Resource.php`
- `use App\Modules\RealEstate\Filament\Resources\<Name>Resource\Pages;` import et
- **Navigation**: `'Real Estate'`
- **Form**:
  - Wizard kullan (4 step: Temel Bilgi → Lokasyon → Özellikler → Görseller)
  - Step 1: title, type, category, rooms_layout, price, currency, area_m2, net_area_m2
  - Step 2: city, district (chain dropdown), address, latitude+longitude (Map field eklentisi varsa)
  - Step 3: building_age, floor, heating type, Feature checkboxlar (Spatie Tags benzeri multiselect)
  - Step 4: FileUpload gallery (multiple, reorderable), cover image
- **Table**:
  - Cover thumbnail, title, city/district, type badge, price, area_m2, rooms_layout, is_published, expires_at
  - Searchable: title, city, district
  - Sortable: price, area_m2, created_at, expires_at

## Adım 5: Public Route + Filter + Vue Page

- **Route prefix**: `emlak` (config/modules.php)
- **Routes**:
  ```php
  Route::get('/', [ListingController::class, 'index'])->name('listing.index');
  Route::get('/{slug}', [ListingController::class, 'show'])->name('listing.show');
  Route::get('/api/markers', [ListingController::class, 'markers']); // bbox bulk fetch
  ```
- **Filter class**: `app/Modules/RealEstate/Filters/ListingFilter.php`
  ```php
  class ListingFilter {
      public function apply(Builder $query, array $params): Builder {
          if (isset($params['city'])) $query->byCity($params['city']);
          if (isset($params['min_price'])) $query->where('price', '>=', $params['min_price']);
          // ...
          return $query;
      }
  }
  ```
- **Vue pages**: `resources/js/Pages/RealEstate/{Index,Show,Map}.vue`
- Pagination: 12-24 per page, classic numbered (SEO için)

## Adım 6: Pest Test

- `tests/Feature/RealEstate/<Name>Test.php`
- Kritik testler:
  - `it lists only published and non-expired listings`
  - `it filters by city and price range`
  - `it shows listing detail with map coordinates`
  - `it returns markers within bbox`
  - `it expires listings via scheduled command`

## Doğrulama

1. Migration ran (`migrate:status`)
2. 81 il + 957 ilçe seed çalıştı (`php artisan db:seed --class=TurkishLocationsSeeder`)
3. Filament'te bir ilan yarat, lat/lng gir, publish et
4. `http://127.0.0.1:8001/emlak` listede görünür
5. `?city=1&min_price=1000000` filtre çalışıyor
6. Detail sayfasında map marker render oluyor

## Anti-Pattern'ler (KAÇIN)

- Lat/lng'yi `varchar` saklamak (DECIMAL kullan)
- City/District'i string saklamak (FK kullan; "İstanbul"/"istanbul"/"Istanbul" çatışması)
- Description'da telefon paylaşımına izin vermek (regex strip)
- "Süresiz" ilan — `expires_at` MUTLAKA dolu
- Sonsuz scroll — pagination kullan (SEO için)
- Map için her listing'i ayrı request — bbox bulk endpoint
- Galeri orijinal full-res images public — `--resize 1600` ile responsive variants üret
- Filtreleri session'a kaydetmek — query string'de tut

## MAMP Lokal Notları

- DB_PORT=8889, password=root, server port 8001
- Local Redis yok → DB driver
- Map tile: OpenStreetMap (ücretsiz) veya Mapbox token'lı
- İlk kurulumda: `php artisan db:seed --class=TurkishLocationsSeeder` (81 il + 957 ilçe)

## Production Cutover Hatırlatma

Production'a çıkarken Redis ayağa kaldır ve şu dördünü `redis`'e çevir:
1. `.env` → `SESSION_DRIVER=redis`
2. `.env` → `CACHE_STORE=redis` ← **emlak'ta filtre cache hız için kritik**
3. `.env` → `QUEUE_CONNECTION=redis`
4. `config/responsecache.php` → `cache_store` `'redis'`

Ek: ilan expire cron — `* * * * *` schedule:run, görsel CDN (Cloudflare/Bunny.net), sitemap.xml cache 1 saat.
