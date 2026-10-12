<?php

namespace App\Support\Routing;

use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;

/**
 * Laravel adreslerin sonundaki "/" işaretini her zaman siler. Kural açıkken (TrailingSlash) bu sınıf onu korur:
 * işaretli rotaların adresleri "/" ile biter; rotasız adreslerde (url('/a/'), url()->current()) verilen "/" korunur.
 */
class UrlGenerator extends BaseUrlGenerator
{
    public function to($path, $extra = [], $secure = null)
    {
        $url = parent::to($path, $extra, $secure);

        $explicit = TrailingSlash::enabled() && $extra === [] && ! $this->isValidUrl($path)
            && self::endsWithSlash($path);

        return $explicit ? TrailingSlash::append($url) : $url;
    }

    public function format($root, $path, $route = null)
    {
        $url = parent::format($root, $path, $route);

        return TrailingSlash::appliesTo($route) ? TrailingSlash::append($url) : $url;
    }

    /** Kök ("/") dışında, yolu (sorgu/parça öncesi) "/" ile biten adres mi? */
    public static function endsWithSlash(string $path): bool
    {
        return (bool) preg_match('~[^/]/(?:[?#]|$)~', $path);
    }
}
