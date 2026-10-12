<?php

namespace App\Support\Routing;

use Closure;

/**
 * Site kökündeki içerik adresleri (/{yol}). Modüller içerik türlerini buraya kaydeder (ör. kökte yayınlanan
 * sayfalar ya da yazılar). Statik rotalar önce eşleşir; kalan yol kayıt sırasıyla çözücülere sorulur ve ilk
 * eşleşen sayfayı üretir. Rota: routes/web.php `content` (en son kayıtlı).
 */
final class ContentPaths
{
    /** @var list<Closure(string): ?Closure> yol → sayfayı üreten closure (app()->call ile çağrılır) ya da null */
    private array $resolvers = [];

    public function register(Closure $resolver): static
    {
        $this->resolvers[] = $resolver;

        return $this;
    }

    public function resolve(string $path): ?Closure
    {
        foreach ($this->resolvers as $resolver) {
            if ($page = $resolver($path)) {
                return $page;
            }
        }

        return null;
    }

    /**
     * Sistem alanlarının (panel, API, Livewire, dosyalar) ilk yol parçaları için regex alternasyonu. Bu alanlar içerik
     * adresi değildir; kök rotası ve LegacyRedirects onları atlar.
     */
    public static function systemPrefixes(): string
    {
        $prefixes = [config('filament.path', 'admin'), config('filament.system_route_prefix', 'filament'),
            'api', 'livewire', 'storage', 'build', 'up'];

        return implode('|', array_map(fn (string $prefix) => preg_quote(trim($prefix, '/'), '~'), array_filter($prefixes)));
    }
}
