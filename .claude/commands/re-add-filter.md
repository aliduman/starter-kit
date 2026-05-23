---
description: RealEstate Listing'e yeni filtre alanı ekler (bina yaşı, cephe, ısıtma tipi, vs.) — migration kolonu, scope, ListingFilter ve frontend UI.
---

`realestate-cms` skill'ini takip ederek mevcut Listing'e yeni bir filtreleme alanı ekle.

Kullanıcı girdisi: $ARGUMENTS  (örnek: "building_age", "heating_type", "facade", "is_furnished")

Yapılacaklar:
1. **Migration**: `php artisan make:migration add_<field>_to_realestate_listings_table` (path `app/Modules/RealEstate/Database/migrations/` altında oluşturulmalı)
   - Uygun tip: enum/boolean/integer/string
   - Nullable + index (filter performans için)
2. **Listing Model güncelle**:
   - `$fillable` ekle
   - `$casts` ekle (boolean/datetime/array için)
   - Scope ekle: `scopeByField($query, $value)` — örn. `scopeByBuildingAge($query, $min, $max)`
3. **`ListingFilter` güncelle**: `app/Modules/RealEstate/Filters/ListingFilter.php`
   ```php
   if (isset($params['<field>'])) {
       $query->by<Field>($params['<field>']);
   }
   ```
4. **Filament Resource form**: yeni alanı uygun field tipiyle ekle (Select/TextInput/Toggle/DatePicker)
5. **Filament Resource table**: filterable kolon ekle
6. **Vue filter UI**: `resources/js/Pages/RealEstate/Index.vue` veya filter sidebar component'ine alan ekle (query string'e bağla)
7. **Pest test**: `it filters listings by <field>`

Önemli:
- Filtre alanlarını **GET query string'de tut**, session'a kaydetme (browser back/forward kırılmasın)
- Çoklu seçim için array notation: `?features[]=elevator&features[]=parking` (Laravel auto-parses)
- Range alanları için `min_<field>` / `max_<field>` kullan (`min_area`, `max_price`)
- Index ekle (büyük tablo için query plan kritik)

Doğrulama: `?<field>=<value>` query string ile listede beklenen filtreleme uygulanıyor mu.
