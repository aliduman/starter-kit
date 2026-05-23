---
description: Ecommerce modülüne yeni bir ödeme gateway'i (PayTR, Param, PayPal, vs.) ekler — PaymentGateway interface'ini implement eder.
---

`ecommerce-cms` skill'ini ve mevcut `app/Modules/Ecommerce/Services/Payment/` klasöründeki StripeGateway/IyzicoGateway pattern'ini takip ederek yeni bir ödeme gateway ekle.

Kullanıcı girdisi: $ARGUMENTS  (örnek: "PayTR", "Param", "PayPal")

Yapılacaklar:
1. **Class oluştur**: `app/Modules/Ecommerce/Services/Payment/<Name>Gateway.php`
   - `implements PaymentGateway`
   - `charge(Order $order): PaymentResult`
   - `refund(Order $order, int $amountCents = null): RefundResult`
   - Constructor'da config'ten credential al
2. **PaymentManager'a register et**: `app/Modules/Ecommerce/Services/Payment/PaymentManager.php`'deki `resolve()` switch'ine `<name>` case'i ekle
3. **.env.example güncelle**: credential placeholder'lar
   ```
   <NAME>_API_KEY=
   <NAME>_SECRET=
   <NAME>_ENV=sandbox
   ```
4. **Config**: `config/services.php` veya yeni `config/payment.php`'ye block ekle:
   ```php
   '<name>' => [
       'key' => env('<NAME>_API_KEY'),
       'secret' => env('<NAME>_SECRET'),
       'env' => env('<NAME>_ENV', 'sandbox'),
   ],
   ```
5. **Webhook route**: `Route::post('/webhooks/<name>', [WebhookController::class, '<name>'])->withoutMiddleware(VerifyCsrfToken::class);`
6. **Webhook handler**: signature verify et + idempotency (`payment_events` tablosunda `event_id` unique)
7. **Pest test**:
   - `it charges via <name> gateway` (HTTP::fake)
   - `it processes <name> webhook idempotently`
   - `it refunds via <name> gateway`
8. **Checkout Vue page'ine** yeni gateway seçeneği ekle (radio button veya logo)

KRİTİK kontroller:
- Credential'ları `.env` dışına ASLA commit etme
- Webhook handler `web` middleware grubunda olmamalı (CSRF token bekler, gateway göndermez)
- 3D Secure varsa redirect URL handle et
- Production'a geçerken sandbox → production env değişimi

Sandbox test çalışıyorsa, gateway dokümantasyonundaki test kart numaralarını kullanarak en az 1 başarılı + 1 başarısız ödeme akışını test et.
