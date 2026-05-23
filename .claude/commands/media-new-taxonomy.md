---
description: Media modülüne yeni taksonomi (Category, Tag, Series, Issue) ekler — hiyerarşik veya flat.
---

`media-cms` skill'ini takip ederek Media modülüne yeni taksonomi ekle.

Kullanıcı girdisi: $ARGUMENTS  (örnek: "Category", "Tag", "Series", "Issue")

Karar ağacı:
- **Hiyerarşik** (Category, Series): parent_id kendi tablosuna FK
- **Flat** (Tag): parent yok
- **Tarihsel** (Issue, dergi sayısı): `publication_date`, `cover_image`, `issue_number`

Yapılacaklar:
1. **Migration**: `media_<plural>` — name, slug (unique), description (text, nullable), position int, hiyerarşik ise parent_id self FK
2. **Pivot tablo** (Article ile ilişki için, Tag/Category ortak): `media_article_<singular>` (article_id, <singular>_id, position)
3. **Model**:
   - `$table = 'media_<plural>';`
   - HasSlug
   - Hiyerarşik için: `parent()` belongsTo self, `children()` hasMany self
   - `articles()` belongsToMany Article (pivot tablosu)
   - Scope: `published()` (Article join ile — sadece published article'ı olanlar)
4. **Article Model güncelle**: yeni taksonomi ile `belongsToMany` ilişki ekle
5. **Filament Resource**: navigation 'Media'. Hiyerarşik ise parent dropdown form'da. Position int sortable.
6. **Public Route**:
   - `Route::get('/<plural>/{taxonomy:slug}', [<Name>Controller::class, 'show'])`
   - List sayfası: `/<plural>` (tüm taksonomileri göster, position'a göre sıralı)
7. **Vue page**: `resources/js/Pages/Media/<Name>.vue` (taksonomi listesi) ve `<Name>Show.vue` (o taksonominin article'ları)
8. **SEO**:
   - Taksonomi sayfası için `seo` JSON kolonu olabilir
   - URL slug `<parent-slug>/<child-slug>` formatında olsun (hiyerarşik için)
9. **Sitemap'e ekle**: taksonomi sayfaları da listed olmalı
10. **Pest test**:
    - `it lists taxonomies with at least one published article`
    - hiyerarşik ise: `it returns children under parent slug URL`
    - `it filters articles by taxonomy slug`

Önemli:
- Hiyerarşik için **slug çatışmasını önle**: `spor/gundem` ile `gundem` çakışmasın → ya prefix'le ya da unique constraint composite (`parent_id, slug`)
- Boş taksonomileri public listede gösterme (`whereHas('articles', fn ($q) => $q->published())`)
- Drag-and-drop sıralama için Filament'in `SortableTrait`'i ekle (mevcut paket varsa)
