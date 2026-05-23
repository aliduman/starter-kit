---
description: RealEstate modülüne yeni ilan tipi veya kategori (daire/villa/arsa/işyeri) ekler.
---

`realestate-cms` skill'ini takip ederek RealEstate modülüne yeni varlık ekle.

Kullanıcı girdisi: $ARGUMENTS  (örnek: "günlük kiralık", "arsa kategorisi", "depo tipi")

Sırasıyla:
1. **Migration**: `realestate_<plural>` tablosu (veya mevcut Listing'e kolon ekleme)
   - Para: `decimal(12, 2)`, currency: `string(3)`
   - Lat/lng: `decimal(10, 7)` — **ASLA varchar**
   - area_m2: `decimal(8, 2)`, net_area_m2 ayrı
   - rooms_layout: string ('3+1' Türkiye notation), rooms/bathrooms integer
   - expires_at: timestamp (her ilan süreli olmalı)
   - Index'ler: `(city_id, district_id, is_published, expires_at)`, `price`, `area_m2`
2. **Model**: `$table = 'realestate_<plural>';`, HasSlug ({city}-{district}-{type}-{rooms}-{id} format), InteractsWithMedia ('gallery', 'cover' collections)
3. **Scope'lar**: `published()` (`is_published AND expires_at > now()`), `byCity()`, `byDistrict()`, `priceRange()`, `areaRange()`
4. **Filament Resource**: Wizard (4 step — Temel/Lokasyon/Özellikler/Görseller). Pages import. Navigation 'Real Estate'.
5. **Public Route**: prefix `emlak`. Filter class `app/Modules/RealEstate/Filters/ListingFilter.php` — query string params'ı Eloquent builder'a apply
6. **Vue pages**: `resources/js/Pages/RealEstate/{Index,Show,Map}.vue`. Pagination classic (sonsuz scroll yok).
7. **Marker endpoint**: `/api/markers?bbox=...` bulk fetch (her marker için ayrı request yapma)
8. **Pest test**: published-and-not-expired filter, city/price filter, bbox markers, expire cron

Sonrasında: `php artisan migrate`, varsa il/ilçe seed (`db:seed --class=TurkishLocationsSeeder`), `php artisan test --filter=...`, `curl -I http://127.0.0.1:8001/emlak` 200.

Domain sorusu (oda notation, m² brüt/net, tapu durumu) için `realestate-architect` agent'ını çağır.
