<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\Homepage\HomepageBlocks;
use App\Support\ResponseCache\ScheduledCacheBoundaries;
use App\Support\Routing\ContentPaths;
use App\Support\Routing\LegacyRedirects;
use App\Support\Routing\LengthAwarePaginator;
use App\Support\Routing\UrlGenerator;
use App\Support\Search\SearchSources;
use App\Support\Seo\Seo;
use App\Support\Seo\Sitemap;
use App\Support\Settings\SettingFields;
use App\Support\Taxonomy\CategoryTypes;
use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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
        $this->app->singleton(SettingFields::class);
        $this->app->singleton(HomepageBlocks::class);
        $this->app->singleton(CategoryTypes::class);
        $this->app->singleton(SearchSources::class);
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

        // Panelde super-admin her izne sahiptir (rol listesi: config/permissions.php).
        Gate::before(fn ($user) => $user->hasRole('super-admin') ? true : null);

        $this->registerSettings();
    }

    /** Her sitede bulunan ayarlar; modüller kendi alanlarını provider'larında ekler. */
    private function registerSettings(): void
    {
        $this->app->make(SettingFields::class)
            ->group('general', 'Genel')
            ->group('contact', 'İletişim')
            ->group('social', 'Sosyal Medya')
            ->group('integrations', 'Entegrasyonlar')
            ->add([
                'site_name' => ['label' => 'Site adı', 'public' => true, 'help' => 'Boş bırakılırsa APP_NAME kullanılır.'],
                'site_description' => ['label' => 'Site açıklaması', 'type' => 'textarea', 'help' => 'Ana sayfanın arama motoru açıklaması.'],
                'privacy_url' => ['label' => 'Aydınlatma metni adresi (KVKK)', 'public' => true, 'help' => 'Formlardaki onay kutusu bu sayfaya bağlanır.'],
                'contact_email' => ['label' => 'E-posta', 'group' => 'contact', 'type' => 'email', 'public' => true],
                'contact_phone' => ['label' => 'Telefon', 'group' => 'contact', 'public' => true],
                'contact_address' => ['label' => 'Adres', 'group' => 'contact', 'type' => 'textarea', 'public' => true],
                'social_instagram' => ['label' => 'Instagram', 'group' => 'social', 'type' => 'url', 'public' => true],
                'social_youtube' => ['label' => 'YouTube', 'group' => 'social', 'type' => 'url', 'public' => true],
                'social_x' => ['label' => 'X (Twitter)', 'group' => 'social', 'type' => 'url', 'public' => true],
                'social_facebook' => ['label' => 'Facebook', 'group' => 'social', 'type' => 'url', 'public' => true],
                'ga4_id' => ['label' => 'Google Analytics 4 ölçüm kimliği', 'group' => 'integrations', 'help' => 'Ör. G-XXXXXXX. Çerez onayından sonra yüklenir.'],
                'google_site_verification' => ['label' => 'Google Search Console doğrulama kodu', 'group' => 'integrations'],
            ]);

        $this->app->make(Seo::class)->resolveDefaultsUsing(fn () => [
            'site_name' => Setting::get('site_name'),
            'description' => Setting::get('site_description'),
            'same_as' => array_values(array_filter(array_map(Setting::get(...), ['social_instagram', 'social_youtube', 'social_x', 'social_facebook']))),
            'verification' => array_filter(['google-site-verification' => Setting::get('google_site_verification')]),
            'analytics' => array_filter(['ga4' => Setting::get('ga4_id')]),
            'search_url' => $this->app->make(SearchSources::class)->isEmpty() ? null : route('search', absolute: false).'?q={search_term_string}',
        ]);
    }
}
