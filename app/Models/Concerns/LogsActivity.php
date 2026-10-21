<?php

namespace App\Models\Concerns;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Panelde "İşlem Geçmişi": kaydı kimin, ne zaman ekleyip değiştirdiği ya da sildiği. Değişikliklerde eski ve yeni
 * değerler saklanır; zaman damgaları, gizli alanlar ve modelin `$activityIgnored` listesi kaydedilmez. Uzun metinler
 * kısaltılır (geçmiş tablosu içeriğin kopyası değildir).
 */
trait LogsActivity
{
    private const ACTIVITY_VALUE_LIMIT = 300;

    protected static function bootLogsActivity(): void
    {
        static::created(fn (Model $model) => Activity::record('created', $model, $model->activityChanges(created: true)));

        static::updated(function (Model $model): void {
            if ($changes = $model->activityChanges()) {
                Activity::record('updated', $model, $changes);
            }
        });

        static::deleted(fn (Model $model) => Activity::record('deleted', $model));
    }

    /** @return array<string, array{old?: mixed, new: mixed}> */
    protected function activityChanges(bool $created = false): array
    {
        $ignored = [
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), ...$this->getHidden(),
            ...(property_exists($this, 'activityIgnored') ? $this->activityIgnored : []),
        ];
        $changes = [];

        foreach ($created ? $this->getAttributes() : $this->getChanges() as $key => $value) {
            if (in_array($key, $ignored, true) || ($created && $value === null)) {
                continue;
            }

            $changes[$key] = $created
                ? ['new' => $this->activityValue($value)]
                : ['old' => $this->activityValue($this->getRawOriginal($key)), 'new' => $this->activityValue($value)];
        }

        return $changes;
    }

    private function activityValue(mixed $value): mixed
    {
        return is_string($value) ? Str::limit(strip_tags($value), self::ACTIVITY_VALUE_LIMIT) : $value;
    }
}
