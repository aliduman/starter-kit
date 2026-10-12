<?php

namespace App\Support\ResponseCache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

/**
 * Inertia uyumlu response cache profili.
 *
 * Sorun: varsayılan profil, normal HTML isteği ile Inertia XHR isteğini
 * aynı cache anahtarında tutar; Inertia isteğine cache'lenmiş HTML dönünce
 * frontend boş bir hata modal'ı gösterir.
 *
 * Çözüm: cache anahtarı istek türüne (html / inertia + asset versiyonu)
 * ve kullanıcıya göre ayrışır.
 */
class InertiaAwareCacheProfile extends CacheAllSuccessfulGetRequests
{
    public function shouldCacheRequest(Request $request): bool
    {
        if ($this->isRunningInConsole()) {
            return false;
        }

        // Inertia XHR'ları cache'le, diğer ajax isteklerini cache'leme
        if ($request->ajax() && ! $request->header('X-Inertia')) {
            return false;
        }

        return $request->isMethod('get');
    }

    public function useCacheNameSuffix(Request $request): string
    {
        $user = (string) ($request->user()?->id ?? 'guest');

        $type = $request->header('X-Inertia')
            ? 'inertia:'.($request->header('X-Inertia-Version') ?? '')
            : 'html';

        return "{$user}/{$type}";
    }
}
