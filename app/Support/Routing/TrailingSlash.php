<?php

namespace App\Support\Routing;

use Illuminate\Routing\Route;

/**
 * Sondaki "/" kuralı (config/seo.php `trailing_slash`). Açıkken içerik rotalarının adresleri "/" ile biter (ör. önceki
 * sitenin dizinlenmiş adresleri öyleyse); kapalıyken "/" olmadan. Rota bu middleware ile işaretlenir: adresleri
 * kurala göre üretilir (UrlGenerator, resources/js/route.js) ve kurala uymayan GET istekleri tek seferlik 301 ile
 * doğru adrese gider (EnforceTrailingSlash).
 */
final class TrailingSlash
{
    public const MIDDLEWARE = 'trailingSlash';

    public static function enabled(): bool
    {
        return (bool) config('seo.trailing_slash');
    }

    /** Rota işaretli ve kural açık mı: adresi "/" ile bitmeli. */
    public static function appliesTo(?Route $route): bool
    {
        return self::enabled() && $route !== null && in_array(self::MIDDLEWARE, $route->middleware(), true);
    }

    /** "/a?b=1" → "/a/?b=1". Sorgu ve parça (#) korunur; zaten "/" ile biten adres değişmez. */
    public static function append(string $url): string
    {
        $end = strcspn($url, '?#');
        $path = substr($url, 0, $end);

        return $path === '' || str_ends_with($path, '/') ? $url : $path.'/'.substr($url, $end);
    }
}
