<?php

namespace App\Support\ResponseCache;

use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Response cache'i içerik değişince temizler. Cache profili girişleri istek türüne (HTML/Inertia) ve kullanıcıya göre
 * ayırdığından (InertiaAwareCacheProfile) adres bazlı silme eski varyantları kaçırır; tümü temizlenir.
 */
class ResponseCacheInvalidator
{
    public function clear(): void
    {
        if (! config('responsecache.enabled')) {
            return;
        }

        ResponseCache::clear();
    }
}
