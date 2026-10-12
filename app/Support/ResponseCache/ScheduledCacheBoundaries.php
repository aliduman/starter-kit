<?php

namespace App\Support\ResponseCache;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Zamanı gelince görünürlüğü değişen içerikler (zamanlanmış yayın, tarih aralıklı içerik) kaydedilmeden
 * görünür ya da gizli olur; kayıt anındaki cache temizliği bunu yakalamaz. Modüller bu sınır kolonlarını
 * register() ile bildirir, scheduler her dakika purgeIfCrossed() çağırır: son kontrolden bu yana bir sınır
 * geçildiyse response cache temizlenir.
 */
class ScheduledCacheBoundaries
{
    private const CHECKED_AT_KEY = 'responsecache.boundaries_checked_at';

    /** İlk çalışmada (veya cache temizlendikten sonra) geriye bakılacak süre. */
    private const FIRST_RUN_LOOKBACK_MINUTES = 60;

    /** @var array<class-string<Model>, list<string>> */
    private array $boundaries = [];

    /**
     * @param  class-string<Model>  $model
     * @param  list<string>  $columns
     */
    public function register(string $model, array $columns): static
    {
        if ($columns === []) {
            return $this;
        }

        $this->boundaries[$model] = array_values(array_unique([...($this->boundaries[$model] ?? []), ...$columns]));

        return $this;
    }

    public function purgeIfCrossed(): bool
    {
        $now = now();
        $checkedAt = Cache::get(self::CHECKED_AT_KEY);
        $since = $checkedAt
            ? $now->copy()->setTimestamp((int) $checkedAt)
            : $now->copy()->subMinutes(self::FIRST_RUN_LOOKBACK_MINUTES);

        Cache::forever(self::CHECKED_AT_KEY, $now->getTimestamp());

        if (! $this->crossedBetween($since, $now)) {
            return false;
        }

        app(ResponseCacheInvalidator::class)->clear();

        return true;
    }

    /** ($from, $to] aralığında herhangi bir kayıtlı sınır kolonu geçildi mi? */
    public function crossedBetween(CarbonInterface $from, CarbonInterface $to): bool
    {
        foreach ($this->boundaries as $model => $columns) {
            $crossed = $model::query()
                ->where(function ($query) use ($columns, $from, $to) {
                    foreach ($columns as $column) {
                        $query->orWhere(fn ($q) => $q->where($column, '>', $from)->where($column, '<=', $to));
                    }
                })
                ->exists();

            if ($crossed) {
                return true;
            }
        }

        return false;
    }
}
