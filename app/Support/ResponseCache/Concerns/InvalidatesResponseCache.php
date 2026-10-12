<?php

namespace App\Support\ResponseCache\Concerns;

use App\Support\ResponseCache\ResponseCacheInvalidator;

/**
 * Herkese açık sayfalarda görünen modeller: kayıt eklenince, değişince ya da silinince response cache temizlenir.
 * Yalnızca görünmeyen alanları değişen kayıtlar için shouldInvalidateResponseCacheOnSave() ezilebilir.
 */
trait InvalidatesResponseCache
{
    protected static function bootInvalidatesResponseCache(): void
    {
        static::saved(function ($model): void {
            if ($model->shouldInvalidateResponseCacheOnSave()) {
                app(ResponseCacheInvalidator::class)->clear();
            }
        });

        static::deleted(fn () => app(ResponseCacheInvalidator::class)->clear());
    }

    protected function shouldInvalidateResponseCacheOnSave(): bool
    {
        return true;
    }
}
