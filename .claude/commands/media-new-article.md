---
description: Media modülüne yeni Article varyantı veya feature (manşet, dosya konusu, yazar köşesi) ekler.
---

`media-cms` skill'ini takip ederek Media modülüne yeni varlık veya feature ekle.

Kullanıcı girdisi: $ARGUMENTS  (örnek: "Article model", "Author profiles", "Comments", "Breaking news bar")

Sırasıyla:
1. **Migration**: `media_<plural>` — title, slug (unique), excerpt, body (longText), category_id, author_id, status enum, is_breaking, is_featured, published_at (indexed), views_count, seo JSON. **Index `(status, published_at)` zorunlu.**
2. **Model**: `$table = 'media_<plural>';`, HasSlug + InteractsWithMedia. Status enum cast.
3. **KRİTİK scope**: `published()` = `WHERE status='published' AND published_at <= NOW()`. **Her listing query bunu kullanmalı**, asla raw query atma.
4. **301 redirect tablosu**: slug değişirse `media_article_redirects` (old_slug → new_slug) — model observer ile otomatik yaz
5. **Filament Resource**: status badge color'lar, "Publish Now" custom action, DateTimePicker future-dated için, TagsInput
6. **Public Route**: prefix `haber`. Slug parameter'ı `Route::get('/{slug}', ...)`. Eski slug 301 redirect handler.
7. **SEO endpoint'leri**: `/feed`, `/sitemap.xml`, `/amp/{slug}` — cache TTL kısa-orta
8. **Vue pages**: `resources/js/Pages/Media/{Index,Show,Category,Author,Tag}.vue`. AMP için ayrı Blade (Vue değil).
9. **Related article servisi**: aynı kategoriden 3 + ortak tag'li 2 (dedupe), cache 1 saat
10. **Pest test**: 
    - `it does not show future-dated articles`
    - `it redirects old slug to new slug 301`
    - `it generates valid RSS XML`
    - `it shows related articles by category + tag overlap`

Sonrasında: `php artisan migrate`, `php artisan test --filter=...`, `curl -I http://127.0.0.1:8001/haber` 200, `curl http://127.0.0.1:8001/haber/feed` ile RSS valid mi kontrol et.

Editöryal akış soruları (draft → review → published transitions, KVKK/5651 compliance) için `media-architect` agent'ını çağır.
