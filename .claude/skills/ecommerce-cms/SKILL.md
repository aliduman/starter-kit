---
name: ecommerce-cms
description: Ecommerce (e-ticaret) projesinde yeni varlık (Product, Variant, Category, Order, Coupon, PaymentGateway) eklemek için adım adım rehber. Para hesabı server-side, stok lock, webhook idempotency dahil.
---

# Ecommerce CMS — Feature Ekleme Rehberi

`app/Modules/Ecommerce` modülünde feature ekleme adımları. Domain bilgisi ve Türkiye-özel notlar için `ecommerce-architect` agent'ı çağır. **Bu modülde hata = finansal kayıp.**

## Adım 1: Domain analizi

Karar ağacı:
- **Satılabilir bir ürün mü?** → `Product` veya `Product` + `Variant`
- **Bir ürün varyasyonu mu?** (beden/renk) → `Variant` (parent product_id ile)
- **Sınıflandırma mı?** → `Category` (hiyerarşik) veya `Tag` (flat)
- **Bir indirim mekanizması mı?** → `Coupon` (kod, kategori, ürün, kullanıcı seviyesinde)
- **Sipariş akışında bir aşama mı?** → `Order` state machine'ine event ekle
- **Yeni ödeme yöntemi mi?** → `PaymentGateway` interface'i implement et (mevcut StripeGateway/IyzicoGateway pattern'ini taklit et)

## Adım 2: Migration

- **Path**: `app/Modules/Ecommerce/Database/migrations/YYYY_MM_DD_HHmmss_create_<name>_table.php`
- **Tablo adı**: `ecommerce_<plural>` — örn `ecommerce_products`, `ecommerce_orders`, `ecommerce_coupons`
- **Para alanları**:
  - `decimal(12, 2)` (TL/USD/EUR için 9,999,999,999.99 üst limit, virgülden sonra 2 hane)
  - **veya** integer (kuruş cinsinden) — daha güvenli, yuvarlama hatası yok
  - **ASLA `float` kullanma**
- **Currency**: `string(3)` ISO 4217 ('TRY', 'USD', 'EUR')
- **Stok**: `integer`, default 0
- **Order kolonları**: `number` (unique, formatted: `ORD-2026-00001`), `status` (enum), `payment_status` (pending/paid/failed/refunded), `subtotal`, `tax`, `shipping`, `total`, `currency`
- **Webhook idempotency için**: `payment_events` tablosu — `event_id` unique kolon
- **VAT**: Product'a `vat_rate` decimal(5,2) — TR için %20/%10/%1

## Adım 3: Eloquent Model

- **Path**: `app/Modules/Ecommerce/Models/<Name>.php`
- **`$table = 'ecommerce_<plural>';`** ZORUNLU
- **$casts**:
  - Para: `decimal:2` (string döner — Money objesi gibi davran)
  - `is_active`, `is_featured` → boolean
  - JSON alanlar (attributes, meta) → array
- **Scope'lar**:
  - `active()` — `is_active=true`
  - `inStock()` — `stock > 0`
  - `published()` — Order için `payment_status='paid'`
- **Relations**:
  - Product hasMany Variant
  - Order hasMany OrderItem
  - Order belongsTo User
  - Product belongsToMany Category

## Adım 4: Filament Resource

- **Path**: `app/Modules/Ecommerce/Filament/Resources/<Name>Resource.php`
- `use App\Modules\Ecommerce\Filament\Resources\<Name>Resource\Pages;` import'unu unutma
- **Navigation**: `'E-Commerce'`
- **Form**:
  - Product: TextInput (name, sku), RichEditor (description), TextInput (price, compare_price, stock), Select (currency, category), Toggle (is_active), FileUpload (gallery via MediaLibrary)
  - Order: read-mostly; status değişimi custom action ile ("Mark as Shipped", "Refund")
- **Table**:
  - Product: thumbnail, name, sku, price, stock badge (red < 5, yellow < 20, green >= 20), is_active toggle
  - Order: number, customer, total, payment_status badge, status, created_at
- **Custom Actions** (Order için):
  - `ship` — status `paid` → `shipped`, OrderShipped event fırlat, tracking code prompt
  - `refund` — PaymentGateway::refund() çağır, status `refunded`
- **Filters**: status, payment_status, date range, is_featured

## Adım 5: Public Route + Controller + Vue Page

- **Route prefix**: `shop` (config/modules.php)
- **Routes** (örnek):
  ```php
  Route::get('/', [ProductController::class, 'index'])->name('shop.index');
  Route::get('/{slug}', [ProductController::class, 'show'])->name('shop.show');
  Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
  Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
  Route::post('/checkout', [CheckoutController::class, 'process']);
  ```
- **Webhook routes** (CSRF'siz ayrı grup):
  ```php
  Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->withoutMiddleware(VerifyCsrfToken::class);
  Route::post('/webhooks/iyzico', [WebhookController::class, 'iyzico']);
  ```
- **Vue pages**: `resources/js/Pages/Ecommerce/{Index,Show,Cart,Checkout,Success}.vue`

## Adım 6: PaymentGateway (yeni gateway eklerken)

1. Class oluştur: `app/Modules/Ecommerce/Services/Payment/<Name>Gateway.php`
2. `implements PaymentGateway` — `charge(Order $order)` ve `refund(Order $order, int $amountCents = null)` methodları
3. `PaymentManager::resolve($driver)` switch'ine ekle
4. `.env.example`'a credential placeholder'ları (`<NAME>_API_KEY`, `<NAME>_SECRET`)
5. `config/services.php` veya `config/modules.php`'ye config block ekle
6. Test: feature test ile mock HTTP yanıtları

## Adım 7: Pest Test

- `tests/Feature/Ecommerce/<Name>Test.php`
- Kritik testler:
  - `it calculates total server-side` — client'tan farklı total gönderirsen reject etmeli
  - `it does not decrease stock on cart add` — stok düşüşü sadece order paid sonrası
  - `it processes webhook idempotently` — aynı event_id iki kez gelirse 2. seferde no-op
  - `it refunds via gateway and updates status`
  - `it applies coupon within usage limit`

## Doğrulama

1. Migration ran
2. Filament Order/Product listede açılır
3. Public `/shop` route render olur
4. Test bir Order üret, Stripe test mode ile ödet, webhook ile `paid` olduğunu doğrula
5. Refund flow test et

## Anti-Pattern'ler (KAÇIN)

- **Client'tan total almak** — server-side recompute zorunlu
- Para alanı `float`
- Webhook'u `web` middleware grubuna koyup CSRF token bekletmek
- Sepete eklenince stok düşürmek (rezerve değil sayım — checkout'a kadar bekle)
- Kupon `usage_count` track etmemek
- `is_active=false` ürünleri public route'ta açık bırakmak (global scope kullan)
- iyzico/Stripe credential'larını `config/` içine commit etmek (sadece `.env`)
- E-fatura entegrasyonunu çekirdeğe gömmek — adapter pattern (PaymentGateway gibi)

## MAMP Lokal Notları

- DB_PORT=8889, DB_USERNAME=root, DB_PASSWORD=root
- Server `php artisan serve --port=8001`, Vite `npm run dev`
- Local Redis yok → SESSION/CACHE/QUEUE database driver
- Stripe test: `stripe listen --forward-to localhost:8001/webhooks/stripe`
- iyzico sandbox: `IYZICO_ENV=sandbox`, API key sandbox panelinden

## Production Cutover Hatırlatma

Production'a çıkarken Redis ayağa kaldır ve şu dördünü `redis`'e çevir:
1. `.env` → `SESSION_DRIVER=redis`
2. `.env` → `CACHE_STORE=redis`
3. `.env` → `QUEUE_CONNECTION=redis` ← **e-ticaret için kritik**, webhook job'ları queue'da
4. `config/responsecache.php` → `cache_store` `'redis'`

Ek olarak: `IYZICO_ENV=production`, `STRIPE_KEY/SECRET` live key, `APP_DEBUG=false`, Supervisor queue worker `redis` connection'ı dinler.
