<?php

namespace App\Support\Seo\Analysis;

use Closure;

/**
 * Analiz edilen içeriğin arama motorunun göreceği hali. Modeller (SeoAnalyzable) kayıtlı ya da formdaki
 * kaydedilmemiş hallerinden üretir; denetimler yalnızca buna bakar.
 */
final class SeoSubject
{
    private ?SeoContent $content = null;

    private ?int $keywordUseCount = null;

    /**
     * @param  string  $title  Arama sonucundaki tam başlık (site adıyla; Seo::pageTitle).
     * @param  ?string  $description  Etkin açıklama: meta açıklama ya da yerine kullanılan özet/metin (HTML olabilir).
     * @param  bool  $descriptionIsAuto  Meta açıklama boş, açıklama yedekten geliyor.
     * @param  ?string  $html  Sayfanın ana metni; null ise metin denetimleri atlanır (ör. etiket sayfası).
     * @param  array{width: ?int, height: ?int}|null  $image  Paylaşım görseli; ölçüsü okunamazsa width/height null.
     * @param  bool  $expectsImage  Görsel yoksa uyarılsın mı.
     * @param  ?Closure(): int  $keywordUsesResolver  Aynı odak anahtar kelimeyi kullanan başka kayıt sayısı (gerekince sorgulanır).
     */
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly ?string $slug = null,
        public readonly ?string $description = null,
        public readonly bool $descriptionIsAuto = false,
        public readonly ?string $html = null,
        public readonly ?string $keyword = null,
        public readonly ?array $image = null,
        public readonly bool $expectsImage = false,
        private readonly ?Closure $keywordUsesResolver = null,
    ) {}

    public function content(): ?SeoContent
    {
        if ($this->html === null) {
            return null;
        }

        return $this->content ??= SeoContent::fromHtml($this->html);
    }

    public function plainDescription(): ?string
    {
        return SeoText::plain($this->description) ?: null;
    }

    public function keywordUses(): int
    {
        return $this->keywordUseCount ??= $this->keywordUsesResolver ? ($this->keywordUsesResolver)() : 0;
    }

    /** @param  array{width: ?int, height: ?int}|null  $image */
    public function withImage(?array $image): self
    {
        return new self(
            $this->title, $this->url, $this->slug, $this->description, $this->descriptionIsAuto, $this->html,
            $this->keyword, $image, $this->expectsImage, $this->keywordUsesResolver,
        );
    }

    /** @return array{width: ?int, height: ?int} */
    public static function imageAt(?string $path): array
    {
        $size = $path && is_file($path) ? @getimagesize($path) : false;

        return ['width' => $size[0] ?? null, 'height' => $size[1] ?? null];
    }
}
