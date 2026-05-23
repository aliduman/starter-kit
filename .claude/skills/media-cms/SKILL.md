---
name: media-cms
description: Media (haber/yayıncılık) projesinde yeni varlık (Article, Category, Tag, Author, Comment) eklemek için adım adım rehber. Editöryal akış (draft→review→published), AMP/RSS, related-by-tag, breaking news dahil.
---

# Media CMS — Feature Ekleme Rehberi

`app/Modules/Media` modülünde feature ekleme. Domain için `media-architect` agent'ı çağır. SEO + okuyucu trafiği kritik — performansa ve URL stabilitesine dikkat.

## Adım 1: Domain analizi

Karar ağacı:
- **Bir makale/haber mi?** → `Article` varyantı
- **Kategori mi?** (hiyerarşik) → `Category` (parent_id)
- **Etiket mi?** (flat) → `Tag`
- **Yazar profili mi?** → `Author` (User'a 1-1) veya doğrudan User'a bio kolonu
- **Yorum sistemi mi?** → `Comment` (moderation default açık)
- **Dergi/sayı mı?** → `Issue` + Article pivot
- **Manuel related listesi mi?** → `RelatedArticle` pivot
- **AMP/RSS feed eklenecek mi?** → ayrı route + Blade template

## Adım 2: Migration

- **Path**: `app/Modules/Media/Database/migrations/YYYY_MM_DD_HHmmss_*.php`
- **Tablo adı**: `media_<plural>` — `media_articles`, `media_categories`, `media_tags`
- **Article kolonları**:
  - `title`, `slug` (unique, indexed)
  - `excerpt` (text) — spot (Türkiye konvansiyonu)
  - `body` (longText) — Rich Editor içeriği
  - `category_id` (FK)
  - `author_id` (FK → users.id)
  - `status` enum: `draft`, `review`, `scheduled`, `published`, `archived`
  - `is_breaking`, `is_featured`, `is_columnist` (boolean)
  - `published_at` (timestamp, indexed) — future-dated `scheduled` için
  - `views_count` (integer, default 0)
  - `seo` (JSON)
- **Category kolonları**: parent_id (FK self), name, slug, position
- **Tag kolonları**: name, slug
- **Article-Tag pivot**: `media_article_tag` (article_id, tag_id)
- **301 redirect tablosu**: `media_article_redirects` (old_slug, new_slug, created_at)
- **Index'ler**:
  - composite: `(status, published_at)` — listing query için
  - `slug` unique
  - `category_id`, `author_id`, `is_breaking`, `is_featured`

## Adım 3: Eloquent Model

- **Path**: `app/Modules/Media/Models/<Name>.php`
- **`$table = 'media_<plural>';`** ZORUNLU
- **Trait'ler**:
  - `HasSlug` — slug otomatik
  - `InteractsWithMedia` — manşet görseli, içerik görselleri
- **$casts**: status enum, `published_at` datetime, `seo` array, `is_*` boolean
- **Scope'lar** (kritik):
  - `published()` — `status='published' AND published_at <= NOW()` ← **her listing query bunu kullanmalı**
  - `scheduled()` — `status='scheduled' AND published_at > NOW()`
  - `breaking()` — `is_breaking=true AND published_at > NOW()->subDay()`
  - `byCategory($id)`, `byTag($slug)`, `byAuthor($slug)`
- **Relations**: belongsTo Category, Author; belongsToMany Tag; hasMany Comment, RelatedArticle

## Adım 4: Filament Resource

- **Path**: `app/Modules/Media/Filament/Resources/<Name>Resource.php`
- `use App\Modules\Media\Filament\Resources\<Name>Resource\Pages;` import et
- **Navigation**: `'Media'`
- **Form** (Article):
  - TextInput (title, slug) — slug Rich Editor'den otomatik
  - Textarea (excerpt) — spot
  - RichEditor (body) — TipTap veya CKEditor
  - Select (category, author, status)
  - DateTimePicker (published_at) — future-dated scheduled için
  - Toggle (is_breaking, is_featured)
  - TagsInput (tags — multiple)
  - Fieldset SEO (title, description, og_image, canonical)
  - FileUpload (cover image — MediaLibrary 'cover' collection)
- **Table**:
  - Cover thumbnail, title, category, author, status badge (gray=draft, yellow=review, blue=scheduled, green=published, red=archived), is_breaking badge, published_at, views_count
  - Filter: status, category, author, is_breaking, date range
  - Action: "Publish Now" (status → published, published_at → now)

## Adım 5: Public Route + Controller + Vue/Blade

- **Route prefix**: `haber` (config/modules.php)
- **Routes**:
  ```php
  Route::get('/', [ArticleController::class, 'index'])->name('article.index');
  Route::get('/kategori/{category:slug}', [ArticleController::class, 'category']);
  Route::get('/yazar/{author:slug}', [ArticleController::class, 'author']);
  Route::get('/etiket/{tag:slug}', [ArticleController::class, 'tag']);
  Route::get('/{slug}', [ArticleController::class, 'show'])->name('article.show');

  // SEO endpoint'leri (cache'li)
  Route::get('/feed', [FeedController::class, 'index'])->name('feed');
  Route::get('/feed/{category:slug}', [FeedController::class, 'category']);
  Route::get('/sitemap.xml', [SitemapController::class, 'index']);
  Route::get('/amp/{slug}', [AmpController::class, 'show']);
  ```
- **Vue pages**: `resources/js/Pages/Media/{Index,Show,Category,Author,Tag}.vue`
- **AMP page**: Blade template (`resources/views/media/amp/show.blade.php`) — Vue/Inertia kullanma, AMP HTML kuralları
- **Response cache**: `cacheResponse:300` (5dk) — yüksek trafik için kritik

## Adım 6: Related Article Algoritması

```php
// app/Modules/Media/Services/RelatedArticleService.php
public function for(Article $article, int $limit = 5): Collection {
    $sameCategoryIds = Article::published()
        ->where('category_id', $article->category_id)
        ->where('id', '!=', $article->id)
        ->latest('published_at')
        ->limit(3)
        ->pluck('id');

    $sharedTagIds = Article::published()
        ->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $article->tags->pluck('id')))
        ->where('id', '!=', $article->id)
        ->whereNotIn('id', $sameCategoryIds)
        ->limit(2)
        ->pluck('id');

    return Article::published()->whereIn('id', $sameCategoryIds->merge($sharedTagIds))->get();
}
```
Cache TTL 1 saat.

## Adım 7: Pest Test

- `tests/Feature/Media/<Name>Test.php`
- Kritik testler:
  - `it lists only published and not-future-dated articles`
  - `it scheduled articles become published when published_at passes` (Carbon::setTestNow)
  - `it shows 301 redirect for old slug`
  - `it returns RSS feed with cache headers`
  - `it generates sitemap with only published articles`
  - `it filters articles by category, author, tag`
  - `it shows related articles by category + tag overlap`

## Doğrulama

1. Migration ran
2. Filament Article listede açılır, status=published kayıt yarat
3. `http://127.0.0.1:8001/haber` listede görünür
4. `/haber/{slug}` detail sayfası render
5. `/haber/feed` valid RSS XML döner
6. `/haber/sitemap.xml` XML döner
7. Slug değiştir, eski URL 301 ile yeni URL'e gider

## Anti-Pattern'ler (KAÇIN)

- `published_at` future-dated kayıtları listede göstermek
- Slug değişimini 301 redirect kaydı tutmadan yapmak (SEO katastrofi)
- Article body'i `{!! $article->body !!}` ile XSS olmadan basmak — Rich Editor purify et veya allow-list
- Kategori slug'ını parent slug'la birleştirmeden saklamak (çatışma)
- Yorum moderation default kapalı (5651 + KVKK riski)
- Sonsuz scroll (SEO için pagination + numbered URL)
- Author'ı User ile aynı şey saymak (bio/social/slug Author meta'sında)
- Aynı article'ı birden fazla kategoride array kolonu ile saklamak (pivot tablosu kullan)
- AMP'i Vue/Inertia ile render etmek (AMP HTML kurallarını ihlal eder)

## MAMP Lokal Notları

- DB_PORT=8889, password=root, server port 8001
- Local Redis yok → DB driver
- Scheduled article'lar için cron simülasyonu: `php artisan schedule:work` (lokalde tek seferlik test)
- Search: küçük site için MySQL FULLTEXT yeterli, büyürse Meilisearch

## Production Cutover Hatırlatma

Production'a çıkarken Redis ayağa kaldır ve şu dördünü `redis`'e çevir:
1. `.env` → `SESSION_DRIVER=redis`
2. `.env` → `CACHE_STORE=redis` ← **haber sitesi için response cache hayati**
3. `.env` → `QUEUE_CONNECTION=redis`
4. `config/responsecache.php` → `cache_store` `'redis'`

Ek olarak: Cron `* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1` (scheduled article publish için), CDN (Cloudflare), sitemap cache 1 saat, RSS feed cache 10dk.
