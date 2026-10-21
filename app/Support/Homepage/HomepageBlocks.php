<?php

namespace App\Support\Homepage;

use App\Models\HomepageSection;
use Closure;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Str;

/**
 * Ana sayfa bileşenleri. Modüller bileşenlerini provider'larında kaydeder; panel (Ana Sayfa Bölümleri) hangilerinin
 * hangi sırayla görüneceğini `homepage_sections` tablosunda tutar. Kapalı modülün bileşeni kayıtlı olmadığından
 * görünmez. Her bileşenin Vue karşılığı resources/js/Blocks/{StudlyAnahtar}.vue dosyasıdır ve `data` prop'unu alır.
 */
final class HomepageBlocks
{
    /** @var array<string, array{label: string, data: Closure, form: ?Closure, default: bool}> */
    private array $blocks = [];

    /**
     * @param  Closure(HomepageSection): array  $data  bileşene giden veri
     * @param  (Closure(): list<Component>)|null  $form  paneldeki ayar alanları (adları `settings.` ile başlar)
     * @param  bool  $default  ilk kurulumda (db:seed) açık bölüm olarak eklenir
     */
    public function register(string $key, string $label, Closure $data, ?Closure $form = null, bool $default = true): static
    {
        $this->blocks[$key] = ['label' => $label, 'data' => $data, 'form' => $form, 'default' => $default];

        return $this;
    }

    /** @return array<string, array{label: string, data: Closure, form: ?Closure, default: bool}> */
    public function all(): array
    {
        return $this->blocks;
    }

    public function has(string $key): bool
    {
        return isset($this->blocks[$key]);
    }

    /** @return array<string, string> */
    public function labels(): array
    {
        return array_map(fn (array $block) => $block['label'], $this->blocks);
    }

    /** @return list<Component> */
    public function form(?string $key): array
    {
        $form = $this->blocks[$key]['form'] ?? null;

        return $form ? $form() : [];
    }

    /** @return array{key: string, component: string, title: string, data: array} */
    public function render(HomepageSection $section): array
    {
        $block = $this->blocks[$section->key];

        return [
            'key' => $section->key,
            'component' => Str::studly($section->key),
            'title' => $section->title ?: $block['label'],
            'data' => ($block['data'])($section),
        ];
    }
}
