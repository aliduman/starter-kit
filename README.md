# Starter Kit · Laravel 13 + Vue 3 + Inertia 3 + Filament 5

Modüler, prodüksiyon hedefli bir starter kit. Tek codebase üzerinden 4 farklı dikey:

| Modül       | Açıklama                                          | Default URL |
|-------------|---------------------------------------------------|-------------|
| Corporate   | Kurumsal sayfalar, hakkımızda, iletişim           | `/`         |
| Ecommerce   | Ürün, sepet, ödeme (Stripe + iyzico)              | `/shop`     |
| RealEstate  | Emlak ilanları, filtreli liste                    | `/emlak`    |
| Media       | Haber / makale, kategori, etiket                  | `/haber`    |

Her modül `app/Modules/{Module}` altında izole; `config/modules.php` üzerinden tek tek açılıp kapatılabilir.

---

## 1. Gereksinimler

- PHP **8.4+**
- Composer 2
- Node **24+**
- MySQL **8** (veya SQLite, test için)
- Redis gerekmez: cache, session ve queue varsayılan olarak `database` sürücüsünde (paylaşımlı cPanel uyumlu).
- (Opsiyonel) Docker Desktop — `docker-compose.yml` ile tam ortam ayağa kalkar.

## 2. Kurulum

```bash
git clone <repo> starter-kit
cd starter-kit

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan storage:link

# DB ayarlarını .env içinde güncelledikten sonra
php artisan migrate --seed
```

Frontend dev sunucusu:

```bash
npm run dev
php artisan serve
```

Admin paneli: `http://localhost:8000/admin` · seed kullanıcı:

- e-posta: `admin@example.com`
- şifre: `password`

## 3. Docker ile

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

- App:    `http://localhost:8080`
- Vite:   `http://localhost:5173`
- Mailhog: `http://localhost:8025`

## 4. Modüller

`config/modules.php` içinde her modülün `enabled` bayrağı vardır. `.env` ile de yönetilir:

```env
MODULE_CORPORATE_ENABLED=true
MODULE_ECOMMERCE_ENABLED=true
MODULE_REALESTATE_ENABLED=true
MODULE_MEDIA_ENABLED=true
```

Bir modül `false` yapıldığında ait olduğu provider, route'lar, view'lar, migration'lar, `Console/` komutları ve Filament resource/page/widget'ları yüklenmez. Modül durumu her yerde `App\Support\Modules` üzerinden okunur. Web route adları otomatik olarak `{key}.` (API: `api.{key}.`) önekini alır; `prefix` `.env` ile değiştirilebilir (`MODULE_MEDIA_PREFIX=...`). Yeni modül eklemek için:

1. `app/Modules/Yourname/` altına `Http/Controllers`, `Models`, `Routes`, `Views`, `Providers`, `Database/migrations`, `Filament/{Resources,Pages,Widgets}`, `Console` dizinlerinden gerekenleri aç.
2. `Providers/YournameServiceProvider.php` oluştur.
3. `config/modules.php` listesine satır ekle (`enabled`, `namespace`, `name`, `description`, `prefix`, `provider`). Başka bağlantı gerekmez.

## 5. Ödeme entegrasyonu

`App\Modules\Ecommerce\Services\Payment\PaymentManager` üzerinden `stripe` ve `iyzico` sürücüleri hazır:

```php
$payments = app(\App\Modules\Ecommerce\Services\Payment\PaymentManager::class);
$result   = $payments->driver('stripe')->charge([
    'amount'   => 199.90,
    'currency' => 'TRY',
    'user'     => $request->user(),
]);
```

`.env`:

```
STRIPE_KEY=...
STRIPE_SECRET=...
IYZICO_API_KEY=...
IYZICO_SECRET_KEY=...
IYZICO_BASE_URL=https://sandbox-api.iyzipay.com
```

## 6. Performans

- **OPcache** + **JIT** ayarları `deploy/php/opcache.ini` içinde. Production'da `validate_timestamps=0`, deploy sonrası `php artisan opcache:clear` veya FPM reload.
- **Preload**: `preload.php` üzerinden temel Laravel sınıfları opcache'e gömülür.
- **Response cache**: `spatie/laravel-responsecache` v8; `cacheResponse` middleware'i ile açılır, `file` store'da tutulur. `App\Support\ResponseCache\InertiaAwareCacheProfile` HTML ve Inertia JSON cevaplarını ayrı anahtarlarda saklar. Geçersizleştirme için `php artisan responsecache:clear`.
- **Cache / session / queue**: varsayılan `database`. Redis isteğe bağlı (`CACHE_STORE=redis` vb.).

## 7. Queue worker'ları

`deploy/supervisor/laravel-worker.conf` dosyasını VPS üzerinde `/etc/supervisor/conf.d/` altına kopyala:

```bash
sudo cp deploy/supervisor/laravel-worker.conf /etc/supervisor/conf.d/
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## 8. Test

```bash
./vendor/bin/pest
# veya:
php artisan test
```

CI üzerinde MySQL 8 ile çalışır (bkz. `.github/workflows/deploy.yml`).

## 9. Deploy

**Production hedefi paylaşımlı cPanel'dir:** sürüm paketi (`scripts/build-release.sh` veya
`.github/workflows/release.yml` artifact'ı) panelden yüklenip açılır, ardından `/kurulum/{SETUP_TOKEN}` çalıştırılır.
Adımlar, `.env` ve cron: **[CPANEL_DEPLOY.md](CPANEL_DEPLOY.md)**.

### Eski VPS yolu (Ubuntu + Nginx + PHP-FPM, kullanılmıyor)

`.github/workflows/deploy.yml.disabled` (kapalı) şu akışı izliyordu:

1. Pest test
2. Vite build
3. Rsync ile VPS'e gönderim
4. `composer install --no-dev`, `migrate --force`, `config/route/view/event cache`, `supervisor restart`, `queue:restart`.

Gereken GitHub secret'ları:

| Secret              | Açıklama                                  |
|---------------------|-------------------------------------------|
| `DEPLOY_SSH_KEY`    | VPS'e SSH erişimi için private key        |
| `DEPLOY_HOST`       | sunucu hostname / IP                      |
| `DEPLOY_USER`       | SSH kullanıcısı (ör. `deployer`)          |
| `DEPLOY_PATH`       | uygulama dizini (`/var/www/starter-kit`)  |

Nginx vhost örneği: `deploy/nginx/site.conf`. SSL için `deploy/nginx/site-ssl.conf`.

## SEO ve adresler

- Her Inertia sayfası `head` prop'u gönderir (`App\Support\Seo\Seo::page([...])`: `title`, `description`, `image`,
  `type`, `schema`...). `app.blade.php` etiketleri ilk yüklemede basar, Inertia (`serverHead: true`) gezinmede günceller.
  Vue `<Head>` içine `head-key`'siz `<meta>` koymayın; sunucu etiketleriyle çakışır.
- `/sitemap.xml`: modüller provider'larında `App\Support\Seo\Sitemap::add()` ile adres ekler. `/robots.txt` ve
  `noindex`: `SEO_INDEXABLE` (test hostlarında `false`).
- İçerik modelleri `Publishable` (yayın bayrağı + yayın tarihi, `published()` scope) ve `InvalidatesResponseCache`
  kullanır. Yayın tarihi ileride olan kayıtlar için kolonu `ScheduledCacheBoundaries` kaydeder; cron (`schedule:run`)
  zamanı gelince response cache'i temizler.
- Kökte içerik adresi (`/{yol}`): `App\Support\Routing\ContentPaths::register()`. Değişen adres biçimleri için 301:
  `App\Support\Routing\LegacyRedirects::add()`. Sondaki "/" kuralı: `SEO_TRAILING_SLASH`. Bağlantılarda yolu elle
  yazmayın: PHP'de `route()`, Vue'da `route('ad', parametreler)` (`resources/js/route.js`).

## 10. API (Sanctum)

`routes/api.php` içinde `/api/user` örneği var. Token üretmek için:

```php
$token = $user->createToken('mobile')->plainTextToken;
```

İleride decoupled bir frontend (Nuxt / Next / mobil) için: aynı User modelinde HasApiTokens trait kullanıma açık.

## 11. Klasör yapısı

```
starter-kit/
├── app/
│   ├── Http/ ...
│   ├── Models/
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── ModuleServiceProvider.php
│   │   └── Filament/AdminPanelProvider.php
│   ├── Support/Modules.php
│   └── Modules/
│       ├── Corporate/
│       ├── Ecommerce/{Services/Payment, ...}
│       ├── RealEstate/
│       └── Media/
├── config/
│   ├── modules.php
│   ├── inertia.php
│   └── responsecache.php
├── resources/
│   ├── js/
│   │   ├── Components/{shared,corporate,ecommerce}
│   │   ├── Layouts/AppLayout.vue
│   │   ├── Pages/{Home, Corporate, Ecommerce, RealEstate, Media, Auth}/
│   │   └── Stores/cart.js
│   ├── css/app.css
│   └── views/
│       ├── app.blade.php
│       └── templates/{corporate,ecommerce}/
├── deploy/
│   ├── nginx/{site.conf, site-ssl.conf}
│   ├── php/{Dockerfile, opcache.ini, php.ini}
│   └── supervisor/laravel-worker.conf
├── .github/workflows/deploy.yml
├── docker-compose.yml
└── preload.php
```

## 12. Tek-dikey proje oluşturma (fork akışı)

Starter kit 4 modüllü monorepo. Tek bir dikeyi (örn. sadece Ecommerce) ayrı bir prodüksiyon projesine dönüştürmek istersen:

### 12.1 Fork + clean

```bash
git clone <repo> shop-project && cd shop-project

# istemediğin 3 modülü sil (örnek: sadece Ecommerce kalacak)
rm -rf app/Modules/{Corporate,RealEstate,Media}
rm -rf resources/js/Pages/{Corporate,RealEstate,Media}

# diğer modüllerin Claude rolleri/skills/komutlarını sil
rm .claude/agents/{corporate,realestate,media}-architect.md
rm -rf .claude/skills/{corporate,realestate,media}-cms
rm .claude/commands/{corp-*,re-*,media-*}.md
```

`config/modules.php` — istemediğin modülleri ya tamamen sil ya da `enabled => false` bırak (ileri taşırsan tekrar açabilirsin).

### 12.2 Bağımlılıklar

```bash
composer install        # post-autoload-dump: filament:assets otomatik publish edilir
npm install
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

### 12.3 .env — MAMP lokal kurulum

```env
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=shop_project
DB_USERNAME=root
DB_PASSWORD=root

# Local Redis yok → DB driver (production'da redis'e dönülecek, bkz. §12.7)
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

MAMP MySQL'de hedef veritabanını oluştur:

```bash
mysql -h 127.0.0.1 -P 8889 -uroot -proot -e "CREATE DATABASE shop_project"
```

### 12.4 Migrate + admin user

```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder

php artisan tinker --execute="
\$u = App\Models\User::factory()->create([
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
]);
\$u->assignRole('super-admin');
"
```

### 12.5 Dev sunucusu

```bash
php artisan serve --port=8001   # Laravel
npm run dev                      # Vite (port 5173)
```

### 12.6 Claude Code'u yeniden başlat

`.claude/` içeriği değiştiği için Claude Code session'ını kapat-aç. Sonrası:

- Slash komutları: `/shop-new-product`, `/shop-new-gateway` (ya da ilgili dikey komutları)
- Agent: `Agent({ subagent_type: 'ecommerce-architect', ... })`
- Skill: `Skill({ skill: 'ecommerce-cms', ... })`

### 12.7 Production cutover (Redis'e geçiş)

Local'de Redis yokken DB driver kullanılır. **Production'a çıkarken Redis'i ayağa kaldırıp şu dördünü `redis`'e çevir:**

```env
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

Ek olarak `config/responsecache.php` içinde `cache_store` default değerini `'redis'` yap (veya `.env` üzerinden `RESPONSE_CACHE_DRIVER=redis`).

Sonra:

```bash
php artisan config:cache route:cache view:cache event:cache
sudo supervisorctl restart laravel-worker:*
```

### 12.8 Hızlı doğrulama

```bash
curl -I http://127.0.0.1:8001/             # anasayfa 200
curl -I http://127.0.0.1:8001/shop         # public route 200 (Ecommerce için)
curl -I http://127.0.0.1:8001/admin/login  # Filament login 200
php artisan test                            # Pest yeşil
```

### Dikey-bazlı dosya silme matrisi

| Hedef dikey  | Silinecek modül dizinleri                              | Silinecek Claude dosyaları (agents/skills/commands)                            |
|--------------|---------------------------------------------------------|--------------------------------------------------------------------------------|
| Corporate    | `Ecommerce`, `RealEstate`, `Media`                      | `ecommerce-*`, `realestate-*`, `media-*` + `shop-*`, `re-*`, `media-*`         |
| Ecommerce    | `Corporate`, `RealEstate`, `Media`                      | `corporate-*`, `realestate-*`, `media-*` + `corp-*`, `re-*`, `media-*`         |
| RealEstate   | `Corporate`, `Ecommerce`, `Media`                       | `corporate-*`, `ecommerce-*`, `media-*` + `corp-*`, `shop-*`, `media-*`        |
| Media        | `Corporate`, `Ecommerce`, `RealEstate`                  | `corporate-*`, `ecommerce-*`, `realestate-*` + `corp-*`, `shop-*`, `re-*`      |

Tüm dikeyler için `ui-ux-pro-max` skill'i (`.claude/skills/ui-ux-pro-max/`) korunur — tasarım sistemi her projede işe yarar.

---

## 13. Lisans

MIT.
