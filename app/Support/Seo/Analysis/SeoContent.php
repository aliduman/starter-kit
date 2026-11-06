<?php

namespace App\Support\Seo\Analysis;

use Dom\HTMLDocument;

/** İçerik gövdesinin denetimlerde kullanılan ölçüleri; HTML bir kez ayrıştırılır. */
final class SeoContent
{
    /**
     * @param  list<string>  $words
     * @param  list<string>  $headings  H2–H4 metinleri.
     */
    private function __construct(
        public readonly string $text,
        public readonly array $words,
        public readonly ?string $intro,
        public readonly array $headings,
        public readonly int $images,
        public readonly int $imagesWithoutAlt,
        public readonly int $internalLinks,
        public readonly int $externalLinks,
        public readonly int $sentences,
        public readonly int $syllables,
    ) {}

    public static function fromHtml(string $html): self
    {
        $text = SeoText::plain($html);
        $document = HTMLDocument::createFromString('<!doctype html><body>'.$html.'</body>', LIBXML_NOERROR, 'UTF-8');
        $texts = fn (string $selector) => array_values(array_filter(array_map(
            fn ($node) => SeoText::plain($node->textContent),
            iterator_to_array($document->querySelectorAll($selector)),
        )));

        $images = iterator_to_array($document->querySelectorAll('img'));
        $internal = $external = 0;

        foreach ($document->querySelectorAll('a[href]') as $link) {
            match (self::linkKind((string) $link->getAttribute('href'))) {
                'internal' => $internal++,
                'external' => $external++,
                default => null,
            };
        }

        // Başlık ve liste satırları noktasız biter; onlar da ayrı cümle sayılır.
        $sentences = count(array_filter(
            preg_split('/[.!?…]+(?=\s|$)/u', implode('. ', [$text, ...$texts('h1, h2, h3, h4, h5, h6, li')])),
            fn (string $sentence) => SeoText::words($sentence) !== [],
        ));

        return new self(
            text: $text,
            words: SeoText::words($text),
            intro: $texts('p')[0] ?? null,
            headings: $texts('h2, h3, h4'),
            images: count($images),
            imagesWithoutAlt: count(array_filter($images, fn ($image) => blank($image->getAttribute('alt')))),
            internalLinks: $internal,
            externalLinks: $external,
            sentences: max(1, $sentences),
            syllables: preg_match_all('/[aeıioöuüâîû]/u', SeoText::lower($text)),
        );
    }

    /** Türkçede hece sayısı ünlü sayısıdır; Ateşman okunabilirlik formülü bunu kullanır. */
    public function readability(): float
    {
        $words = max(1, count($this->words));

        return 198.825 - 40.175 * ($this->syllables / $words) - 2.610 * ($words / $this->sentences);
    }

    private static function linkKind(string $href): ?string
    {
        $href = trim($href);

        if ($href === '' || str_starts_with($href, '#') || preg_match('~^(mailto|tel|javascript):~i', $href)) {
            return null;
        }

        $host = parse_url($href, PHP_URL_HOST);

        if (! $host) {
            return 'internal';
        }

        $site = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        return preg_replace('/^www\./', '', strtolower($host)) === preg_replace('/^www\./', '', strtolower($site))
            ? 'internal'
            : 'external';
    }
}
