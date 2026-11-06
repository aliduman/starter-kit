<?php

namespace App\Support\Seo\Analysis;

use App\Support\Seo\Seo;
use Closure;
use Illuminate\Support\Str;

/**
 * SeoAnalyzable modeller için yardımcılar. Modelde `slug` ve `focus_keyword` sütunları, görsel kullanılıyorsa
 * Spatie medya koleksiyonu beklenir.
 */
trait HasSeoAnalysis
{
    public function seoAnalysis(): SeoReport
    {
        return app(SeoAnalyzer::class)->analyze($this->seoSubject());
    }

    /**
     * Ortak alanlarla analiz girdisi; model yalnızca kendine özgü olanları verir.
     *
     * @param  ?string  $title  Site adı eklenmemiş sayfa başlığı.
     * @param  ?string  $metaDescription  Panelde yazılan meta açıklama.
     * @param  ?string  $fallbackDescription  Meta açıklama boşken sayfada kullanılan metin.
     * @param  array{width: ?int, height: ?int}|null  $image
     */
    protected function makeSeoSubject(
        ?string $title,
        string $slugSource,
        ?string $metaDescription = null,
        ?string $fallbackDescription = null,
        ?string $html = null,
        ?array $image = null,
        bool $expectsImage = false,
    ): SeoSubject {
        $slug = $this->slug ?: Str::slug($slugSource);
        $keyword = trim((string) $this->focus_keyword) ?: null;

        return new SeoSubject(
            title: app(Seo::class)->pageTitle($title),
            url: $this->seoUrl($slug),
            slug: $slug ?: null,
            description: filled($metaDescription) ? $metaDescription : $fallbackDescription,
            descriptionIsAuto: blank($metaDescription),
            html: $html,
            keyword: $keyword,
            image: $image,
            expectsImage: $expectsImage,
            keywordUsesResolver: $keyword ? $this->seoKeywordUses($keyword) : null,
        );
    }

    /** @return array{width: ?int, height: ?int}|null */
    protected function seoMediaImage(string $collection): ?array
    {
        $media = $this->exists ? $this->getFirstMedia($collection) : null;

        return $media ? SeoSubject::imageAt($media->getPath()) : null;
    }

    /** Kaydedilmemiş kopyada adres, başlıktan üretilecek slug ile tahmin edilir. */
    private function seoUrl(string $slug): string
    {
        $model = $this->slug ? $this : (clone $this)->forceFill(['slug' => $slug ?: '-']);

        return url($model->url);
    }

    /** @return Closure(): int */
    private function seoKeywordUses(string $keyword): Closure
    {
        // MySQL'in varsayılan karşılaştırması büyük/küçük harf duyarsızdır.
        return fn () => static::query()
            ->where('focus_keyword', $keyword)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->count();
    }
}
