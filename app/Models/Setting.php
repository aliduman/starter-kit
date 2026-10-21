<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Support\ResponseCache\Concerns\InvalidatesResponseCache;
use App\Support\Settings\SettingFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

/** Site ayarı değeri; tanımı (ad, grup, tür, varsayılan) App\Support\Settings\SettingFields'tadır. */
class Setting extends Model
{
    use InvalidatesResponseCache;
    use LogsActivity;

    private const CACHE_KEY = 'settings';

    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** @return array<string, ?string> kayıtlı değerler (anahtar => değer) */
    public static function values(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
        } catch (Throwable) {
            return []; // Kurulumda tablolar yokken (migration öncesi) çağrılırsa; boş sonuç cache'e girmez.
        }
    }

    /** Kayıtlı değer; boşsa alanın varsayılanı, o da yoksa $default. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::values()[$key] ?? null;

        return filled($value) ? $value : (app(SettingFields::class)->get($key)['default'] ?? $default);
    }

    /** Satır satır yazılmış çok değerli ayar (ör. birden fazla telefon); boş satırlar atılır. */
    public static function lines(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) static::get($key))), 'strlen'));
    }

    /** @return array<string, mixed> istemciye giden (public) ayarlar */
    public static function publicValues(): array
    {
        $keys = app(SettingFields::class)->publicKeys();

        return array_combine($keys, array_map(fn (string $key) => static::get($key), $keys));
    }

    /** @param  array<string, ?string>  $values  yalnızca değişen değerler yazılır */
    public static function store(array $values): void
    {
        $current = static::values();

        foreach ($values as $key => $value) {
            if (! array_key_exists($key, $current) || $current[$key] !== $value) {
                static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }
    }
}
