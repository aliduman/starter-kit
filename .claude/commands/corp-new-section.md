---
description: Bir Corporate sayfasının bölümünü (Hero, FeatureList, Testimonial, CTA) ekler.
---

`corporate-cms` skill'ini takip ederek mevcut bir Corporate Page'ine yeni bir Section tipi ekle.

Kullanıcı girdisi: $ARGUMENTS  (örnek: "hero", "feature-list", "testimonial", "cta")

Yapılacaklar:
1. Section tablosu yoksa migration ile yarat: `corporate_sections` (page_id FK, type string, content JSON, position int, is_visible bool)
2. Section model: `app/Modules/Corporate/Models/Section.php` — Page'e belongsTo, `$casts = ['content' => 'array']`
3. Page model'ine `hasMany(Section::class)->orderBy('position')` ilişkisi ekle (yoksa)
4. Filament Page resource'una RelationManager olarak Section'ı ekle (form + repeater veya ayrı CRUD)
5. Section render için Vue component: `resources/js/Components/Sections/<Type>Section.vue` — props `content`
6. Page Show view'ında section type'a göre dynamic component render et:
   ```vue
   <component :is="sectionComponents[s.type]" v-for="s in page.sections" :key="s.id" :content="s.content" />
   ```

Type-specific content şemaları:
- **hero**: `{title, subtitle, image, cta_label, cta_url}`
- **feature-list**: `{items: [{icon, title, description}], columns: 3}`
- **testimonial**: `{quote, author_name, author_role, author_photo}`
- **cta**: `{title, description, button_label, button_url, background}`

Test: section yarat, page'i view et, doğru component render edildiğini doğrula.
