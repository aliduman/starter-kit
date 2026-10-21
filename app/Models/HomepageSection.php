<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Support\Homepage\HomepageBlocks;
use App\Support\ResponseCache\Concerns\InvalidatesResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Ana sayfada görünen bir bileşen, sırası ve ayarları; bileşenler App\Support\Homepage\HomepageBlocks'ta kayıtlı. */
class HomepageSection extends Model
{
    use InvalidatesResponseCache;
    use LogsActivity;

    protected $fillable = ['key', 'title', 'is_active', 'sort_order', 'settings'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'settings' => 'array',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /** Varsayılan açık bileşenleri kayıt sırasıyla ekler; var olanlara dokunmaz (tekrar çalıştırılabilir). */
    public static function installDefaults(): void
    {
        $order = (int) static::query()->max('sort_order');

        foreach (app(HomepageBlocks::class)->all() as $key => $block) {
            if ($block['default']) {
                static::firstOrCreate(['key' => $key], ['is_active' => true, 'sort_order' => ++$order]);
            }
        }
    }
}
