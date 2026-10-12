# cPanel Deploy

Production hedefi **paylaşımlı cPanel** hosting'dir. SSH olmayan hostlarda da çalışır: deploy **elle** yapılır.
Hazır sürüm paketi (zip) panelden yüklenip **Ayıkla** ile açılır, ardından `/kurulum` ile migration ve cache temizliği
çalıştırılır.

## Host gereksinimleri

| Bileşen | Gereksinim |
|---|---|
| PHP | **≥ 8.4** (cPanel MultiPHP). Eklentiler: `mbstring pdo_mysql intl gd zip bcmath exif fileinfo` |
| MySQL | 8.0, `utf8mb4`. Tablolar her zaman InnoDB açılır (`config/database.php`), sunucu varsayılanı MyISAM olsa da. |
| Cron Jobs | `schedule:run` için gerekli |
| Dosya yönetimi | cPanel File Manager: yükleme (Upload / Karşıya yükle) ve zip açma (Extract / Ayıkla) |
| SSL | cPanel AutoSSL |

Redis, Meilisearch, Supervisor ve kalıcı Node süreçleri **yok** varsayılır: cache `file`, session `file`, queue `sync`
(ya da `database` + cron). Composer ve Node sunucuda çalışmaz; paket hazır `vendor/` ve derlenmiş asset'lerle gelir.
Inertia SSR (`npm run build:ssr`) kalıcı Node ister; cPanel'de kullanılmaz. SEO etiketleri sunucuda Blade ile basılır
(`App\Support\Seo\Seo`).

## Sürüm paketi

Paketin içeriği `scripts/build-release.sh` ile tanımlıdır: commit'in temiz kopyası (commit'lenmemiş değişiklikler
girmez), production `vendor/` (`composer install --no-dev`), derlenmiş `public/build` ve
`bootstrap/cache/{packages,services}.php`. **Girmeyenler:** `.env`, `storage/`, testler, Docker/`deploy/` dosyaları,
`.md` belgeleri. Bu yüzden paketi açmak sunucudaki ayarlara ve yüklenen dosyalara dokunmaz. Neyin pakete girmediği
yalnızca script'teki `EXCLUDES` listesinde tanımlıdır.

Paketi almanın iki yolu var:

- **GitHub'dan (önerilen):** `.github/workflows/release.yml` her `main` / `furkan_dev` push'unda testleri çalıştırır ve
  paketi ekler. Actions → ilgili çalıştırma (yeşil olmalı) → **Artifacts** → `<repo>-release-<sha>` indirilir; inen
  zip doğrudan yüklenecek pakettir. 14 gün saklanır. Elle tetiklemek için:

  ```bash
  gh workflow run release.yml --ref furkan_dev
  ```

- **Lokalden:** proje klasöründe (testleri önce çalıştırın):

  ```bash
  scripts/build-release.sh
  ```

  Çıktı: projenin bir üst klasöründe `<klasör-adı>-release-<sha>.zip` (adı `RELEASE_NAME` ile değiştirilebilir).
  Belirli bir commit ve klasör için: `scripts/build-release.sh <commit> <klasör>`.

## Her deploy

Sunucudaki proje klasöründe (`.env` dosyasının olduğu klasör), cPanel File Manager ile. Gizli dosyaları gösterin
(Settings → Show Hidden Files).

1. **Yedek:** phpMyAdmin → veritabanı → **Dışa aktar (Export)**. Şema değişen sürümlerde şart.
2. **Eski kodu kenara al:** `backup/<tarih>/` klasörü oluşturun ve **Taşı (Move)** ile içine taşıyın:
   `app`, `bootstrap`, `config`, `database`, `lang`, `resources`, `routes`, `vendor` ve `public` içindeki yalnızca
   `build` klasörü. **Taşımayın:** `.env`, `storage`, `public` klasörünün kendisi, `.htaccess`, `.user.ini`,
   `.well-known`. Bu andan 4. adıma kadar site hata verir; adımları arka arkaya yapın.

   Neden: zip eski dosyaların üstüne açılırsa projeden silinmiş dosyalar sunucuda kalır (ör. Laravel'in hâlâ
   yüklediği eski bir config dosyası). Taşımak hem temiz kopya hem hazır geri dönüş sağlar.
3. **Yükle:** `<repo>-release-<sha>.zip` → **Upload (Karşıya yükle)**, %100 olana kadar bekleyin.
4. **Aç:** zip'i seçin → **Extract (Ayıkla)** → hedef bu klasör. Var olan dosyaların üzerine yazılır. Ardından zip'i
   silin; `app`, `vendor`, `public/build` vb. geri gelmiş olmalı.
5. **Kurulum:** `.env`'de `SETUP_TOKEN` dolu olmalı. `https://alanadi.com/kurulum/SETUP_TOKEN` adresini **bir kez**
   açın ve sayfa bitene kadar yenilemeyin: migrate + storage:link + response cache + `optimize:clear`. Response cache
   temizliği şart, çünkü cache'teki sayfalar artık olmayan eski `public/build` dosyalarına işaret eder. Çıktıda hata
   olmamalı; `The [public/storage] link already exists` uyarısı zararsızdır. `?cache=1` config/route/view cache
   oluşturur; yalnızca `.env` son halindeyken kullanın.
6. **Kapat:** `.env`'den `SETUP_TOKEN` değerini silin (route 404 döner). Bir sonraki deploy'da yeniden ekleyin.
7. **Kontrol:** ana sayfa, birkaç içerik sayfası, `/admin` girişi, `/robots.txt` (test hostunda `Disallow: /`).
8. **Temizlik:** sorun yoksa birkaç gün sonra `backup/<tarih>/` silinebilir.

**Geri dönüş:** açılan kod klasörlerini silin, `backup/<tarih>/` içindekileri yerlerine taşıyın (`build` →
`public/build`). Şema değiştiyse 1. adımdaki dışa aktarımı phpMyAdmin → **İçe aktar (Import)** ile geri yükleyin.

## İlk kurulum (sunucu başına bir kez, SSH'sız)

1. **PHP:** cPanel → MultiPHP Manager → PHP 8.4.
2. **Veritabanı:** cPanel → MySQL Databases → veritabanı + kullanıcı oluştur, kullanıcıya tüm yetkileri ver.
3. **Document root:** mümkünse domainin document root'unu projenin `public/` klasörüne ayarlayın. Mümkün değilse
   proje web köküne açılır ve kökteki `.htaccess` istekleri `public/` altına yönlendirip hassas dosya ve klasörlere
   erişimi engeller.
4. **`.env`:** File Manager ile proje kökünde elle oluşturun. Bir kez oluşturulur, paket ona dokunmaz. Tüm anahtarlar
   için `.env.example`'a bakın. Asgari:

   ```env
   APP_NAME="Site Adı"
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=            # lokalde üretin, bkz. aşağı. Bir kez ayarlanır, bir daha değişmez.
   APP_URL=https://alanadi.com

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=
   DB_USERNAME=
   DB_PASSWORD=

   CACHE_STORE=file    # response cache de file store'da
   SESSION_DRIVER=file
   QUEUE_CONNECTION=sync

   MAIL_MAILER=smtp    # MAIL_HOST/PORT/USERNAME/PASSWORD/FROM_ADDRESS

   SETUP_TOKEN=        # uzun rastgele değer; /kurulum/{token} için. İş bitince boşaltın.
   SETUP_ADMIN_EMAIL=  # ilk seed'in açacağı admin; production'da boşsa admin oluşturulmaz
   SETUP_ADMIN_PASSWORD=

   SEO_INDEXABLE=false # test/staging hostlarında ŞART (APP_ENV=production olsa da arama motorlarına kapatır);
                       # canlı sitede true yapın
   ```

   `APP_KEY` ve `SETUP_TOKEN` üretmek için (lokalde):

   ```bash
   php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
   ```

5. **Kod:** "Her deploy" 3–4. adımları (yükle + Ayıkla).
6. **Migration + seed:** `https://alanadi.com/kurulum/SETUP_TOKEN?seed=1` → roller/izinler ve `SETUP_ADMIN_*` ile
   admin hesabı oluşur. Çıktıda `== seed ==` bölümü görünmeli. Sonra `.env`'den `SETUP_ADMIN_PASSWORD` ve
   `SETUP_TOKEN` değerlerini silin. Seed tekrar çalıştırılabilir, ama gerek yoksa `?seed=1` kullanmayın.
7. **storage:link:** `/kurulum` bunu dener. Host `symlink()`'i engelliyorsa çıktıda hata görünür; o durumda
   `public/storage` bağlantısını cPanel File Manager/destek ile oluşturun.
8. **Cron:** cPanel → Cron Jobs → her dakika:

   ```
   * * * * * /opt/cpanel/ea-php84/root/usr/bin/php /home/KULLANICI/PROJE/artisan schedule:run >> /dev/null 2>&1
   ```

   PHP yolu hosta göre değişir (MultiPHP'de genellikle `ea-php84`). Hostlarda `exec`/`proc_open` çoğu zaman kapalı
   olduğundan zamanlanmış işler `Schedule::call`/`Schedule::job` ile yazılmalı (`Schedule::command` alt süreç açar).
   **Bu cron şart:** yayın tarihi ileride olan içerikler, zamanı gelince response cache'i temizleyen dakikalık görev
   (`App\Support\ResponseCache\ScheduledCacheBoundaries`) sayesinde görünür. Cron yoksa bir sonraki kayda kadar eski
   sayfa gösterilir.
9. **Kontrol:** `https://alanadi.com/robots.txt` test hostunda `Disallow: /`, canlıda `Sitemap:` satırı göstermeli;
   `https://alanadi.com/sitemap.xml` açılmalı.

## Bilinen sınırlar

- `/kurulum/{token}` HTTP üzerinden migrate/seed çalıştırır. Yalnızca `SETUP_TOKEN` doluyken vardır, dakikada 5
  istekle sınırlıdır ve production'da bilinen şifreli admin açmaz. Token'ı uzun ve gizli tutun, iş bitince silin.
- SSH olmadığı için bakım modu (`artisan down`) yok: 2. adımdan 4. adıma kadar site hata verir.
- MySQL'de şema değişiklikleri (DDL) anında kalıcıdır: yarıda kalan bir migration geri alınmaz. Yeni migration'ları
  tekrar çalıştırılabilir yazın (`Schema::hasColumn` / `hasTable` kontrolleri), `/kurulum`'u bitmeden yenilemeyin.

## Sık karşılaşılan sorunlar

| Sorun | Çözüm |
|---|---|
| Deploy sonrası 500 | `storage/logs/laravel.log`'a File Manager ile bakın (en yeni kayıtlar en altta); `/kurulum/{token}`'ı açtığınızdan emin olun. |
| Sayfalar stilsiz / JS 404 | Response cache eski build'e işaret ediyor → `/kurulum/{token}`'ı tekrar açın. |
| `/kurulum` 404 | `.env`'de `SETUP_TOKEN` yok, URL'deki token eşleşmiyor ya da config cache eski (`?cache=1` sonrası `.env` değiştiyse `bootstrap/cache/config.php`'yi silin). |
| `/kurulum` 429 | Dakikada 5 istek sınırı; bir dakika bekleyin. |
| `Duplicate column name` / `Table already exists` | Önceki çalıştırma yarıda kalmış. Migration tekrar çalıştırılabilir değilse yarım kalan tablo/kolonları phpMyAdmin'den silip tekrar deneyin. |
| `Specified key was too long; max key length is 1000 bytes` | Tablolar MyISAM açılmış. `config/database.php` sunucuda olmalı (InnoDB zorlar); yarım açılan tabloları silip `/kurulum`'u tekrar açın. |
| Migration hatası (erişim) | cPanel DB adı/kullanıcısı `hesap_` önekli olmalı (`DB_DATABASE`, `DB_USERNAME`). |
| Dosya izin hatası | `storage/` ve `bootstrap/cache/` yazılabilir olmalı (File Manager → izinler 755/775). |
| Zip yüklenmiyor | Panelin yükleme sınırı aşılıyor olabilir; hosttan sınırı sorun. |
