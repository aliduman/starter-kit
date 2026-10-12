<?php

namespace App\Support\Routing;

use Closure;
use Illuminate\Http\Request;

/**
 * Eski ya da değişen adres biçimlerinin yeni karşılıklarına kalıcı yönlendirmesi (ör. önceki sitenin sayfalama veya
 * arama adresleri). Kural = yol deseni + hedef adresi üreten closure; modüller kendi kurallarını provider'larında
 * kaydeder, RedirectLegacyUrls middleware'i rota eşleşmeden önce ilk eşleşen kural için 301 döner. Böylece
 * dizinlenmiş adresler 404 vermez.
 *
 * Aynı adres yeni sitede de içerik olabileceğinden closure null dönerek kuralı atlayabilir.
 */
final class LegacyRedirects
{
    /** @var list<array{0: string, 1: Closure(array<int, string>, Request): ?string}> */
    private array $rules = [];

    /** @param  Closure(array<int, string>, Request): ?string  $target  desen eşleşmeleri ve istek → adres ya da null */
    public function add(string $pattern, Closure $target): static
    {
        $this->rules[] = [$pattern, $target];

        return $this;
    }

    public function resolve(Request $request): ?string
    {
        if ($this->rules === []) {
            return null;
        }

        $path = $request->getPathInfo();

        if (preg_match('~^/(?:'.ContentPaths::systemPrefixes().')(?:/|$)~', $path)) {
            return null;
        }

        foreach ($this->rules as [$pattern, $target]) {
            if (preg_match($pattern, $path, $matches) && $url = $target($matches, $request)) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Site içi adres: yol ("" = ana sayfa) ve boş olmayan sorgu parametreleri; "/" kuralı (TrailingSlash) uygulanır.
     *
     * @param  array<string, mixed>  $query
     */
    public static function path(string $path, array $query = []): string
    {
        $path = '/'.trim($path, '/');
        $query = http_build_query(array_filter($query, fn ($value) => $value !== null && $value !== ''));

        if (TrailingSlash::enabled()) {
            $path = TrailingSlash::append($path);
        }

        return $path.($query === '' ? '' : '?'.$query);
    }
}
