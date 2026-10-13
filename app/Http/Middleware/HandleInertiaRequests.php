<?php

namespace App\Http\Middleware;

use App\Support\Modules;
use App\Support\Seo\Seo;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'locale' => app()->getLocale(),
            ],
            // Varsayılan SEO etiketleri; sayfalar kendi `head` prop'unu (Seo::page) gönderince bu ezilir.
            'head' => Seo::page(),
            // Çerez onayından sonra yüklenen ölçüm araçları (resources/js/analytics.js).
            'analytics' => fn () => app(Seo::class)->analytics(),
            'modules' => fn () => collect(Modules::all())
                ->map(fn ($module, $key) => ['enabled' => Modules::isEnabled($key)])
                ->all(),
            'auth' => [
                'user' => fn () => $request->user()
                    ? $request->user()->only('id', 'name', 'email')
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
