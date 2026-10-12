<?php

namespace App\Providers;

use App\Support\ResponseCache\ScheduledCacheBoundaries;
use App\Support\Routing\ContentPaths;
use App\Support\Routing\LegacyRedirects;
use App\Support\Routing\LengthAwarePaginator;
use App\Support\Routing\UrlGenerator;
use App\Support\Seo\Seo;
use App\Support\Seo\Sitemap;
use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Modüllerin provider'larında doldurduğu kayıt defterleri.
        $this->app->singleton(ScheduledCacheBoundaries::class);
        $this->app->singleton(Seo::class);
        $this->app->singleton(Sitemap::class);
        $this->app->singleton(ContentPaths::class);
        $this->app->singleton(LegacyRedirects::class);
        $this->app->bind(BaseLengthAwarePaginator::class, LengthAwarePaginator::class);

        // Framework'ün URL üreticisiyle aynı kurulum (RoutingServiceProvider); yalnızca sınıf değişir. Framework'ün
        // `extend('url')` geri çağrıları (session/key resolver) yeni bağlamada da çalışır.
        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();
            $app->instance('routes', $routes);

            return new UrlGenerator(
                $routes,
                $app->rebinding('request', fn ($app, $request) => $app['url']->setRequest($request)),
                $app['config']['app.asset_url'],
            );
        });
    }

    public function boot(): void
    {
        Paginator::useTailwind();
        // Sayfalama bağlantıları geçerli adresi sondaki "/" dahil korur (bkz. TrailingSlash).
        Paginator::currentPathResolver(fn () => url()->current());

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->app->make(Sitemap::class)->add(fn () => [['loc' => '/']]);
    }
}
