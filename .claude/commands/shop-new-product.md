---
description: Ecommerce modülüne yeni ürün tipi veya ürün özelliği (variant, attribute, category) ekler.
---

`ecommerce-cms` skill'ini takip ederek Ecommerce modülüne yeni bir varlık ekle.

Kullanıcı girdisi: $ARGUMENTS  (örnek: "Product variant support", "Category hierarchy", "Coupon system")

Sırasıyla:
1. Migration: `ecommerce_<plural>` — para alanları DECIMAL(12,2), stok integer, currency ISO 4217 (3 char). **ASLA float kullanma.**
2. Model: `$table = 'ecommerce_<plural>';`, HasSlug + InteractsWithMedia (görsel varsa). `active()`, `inStock()` scope'ları.
3. Filament Resource: Pages import et. Para alanları için `->prefix('₺')->numeric()`. Stok için badge color (red < 5).
4. Public Route: prefix `shop`, Vue pages `resources/js/Pages/Ecommerce/`
5. Server-side total hesaplama kuralına dikkat — client'tan gelen tutarları reject et
6. Pest test:
   - `it does not trust client-side totals`
   - `it does not decrease stock on cart add` (sadece order paid'de)
   - eğer kupon: `it applies coupon within usage_limit`

Sonrasında: `php artisan migrate`, `php artisan test --filter=...`, `curl -I http://127.0.0.1:8001/shop` ile 200 doğrula.

Domain sorusu için `ecommerce-architect` agent'ını çağır (KDV oranları, durum makinesi, webhook idempotency için).
