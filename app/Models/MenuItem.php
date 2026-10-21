<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Support\ResponseCache\Concerns\InvalidatesResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Throwable;

/** Paneldeki menü öğesi. Konumlar ve ilk kurulum öğeleri config/menus.php'dedir; menüler iki düzeylidir. */
class MenuItem extends Model
{
    use InvalidatesResponseCache;
    use LogsActivity;

    private const CACHE_KEY = 'menus';

    public const TARGETS = [
        '_self' => 'Aynı sekme',
        '_blank' => 'Yeni sekme',
    ];

    protected $fillable = [
        'parent_id',
        'location',
        'title',
        'url',
        'target',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** @return array<string, string> */
    public static function locations(): array
    {
        return config('menus.locations', []);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * config('menus.defaults') öğelerini yazar; aynı başlıklı öğe varsa dokunmaz (tekrar çalıştırılabilir). Rotası
     * olmayan (modülü kapalı) öğe atlanır.
     *
     * @param  list<array{title: string, route?: string, url?: string, children?: list<array>}>  $items
     */
    public static function installDefaults(string $location, array $items, ?int $parentId = null): void
    {
        foreach (array_values($items) as $i => $item) {
            if (isset($item['route']) && ! Route::has($item['route'])) {
                continue;
            }

            $menuItem = static::firstOrCreate(
                ['location' => $location, 'title' => $item['title'], 'parent_id' => $parentId],
                [
                    'url' => isset($item['route']) ? route($item['route'], absolute: false) : ($item['url'] ?? null),
                    'target' => '_self',
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );

            static::installDefaults($location, $item['children'] ?? [], $menuItem->id);
        }
    }

    /** @return array<string, list<array>> konum => öğeler (paylaşılan `menus` prop'u) */
    public static function forFrontend(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()
                ->active()
                ->whereNull('parent_id')
                ->with('children')
                ->get()
                ->groupBy('location')
                ->map(fn ($items) => $items->map(fn (MenuItem $item) => $item->toMenuArray())->values()->all())
                ->all());
        } catch (Throwable) {
            return []; // Kurulumda tablolar yokken.
        }
    }

    public function toMenuArray(): array
    {
        return [
            'title' => $this->title,
            'href' => $this->url,
            'target' => $this->target,
            'children' => $this->relationLoaded('children')
                ? $this->children->map(fn (MenuItem $child) => $child->toMenuArray())->values()->all()
                : [],
        ];
    }
}
