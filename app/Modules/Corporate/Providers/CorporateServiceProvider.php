<?php

namespace App\Modules\Corporate\Providers;

use App\Modules\Corporate\Models\Page;
use App\Support\ResponseCache\ScheduledCacheBoundaries;
use App\Support\Seo\Sitemap;
use Illuminate\Support\ServiceProvider;

class CorporateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Yayın tarihi gelen kayıt cache temizlenince görünür (routes/console.php).
        $this->app->make(ScheduledCacheBoundaries::class)->register(Page::class, Page::publicationBoundaries());

        $this->app->make(Sitemap::class)->add(function () {
            yield ['loc' => route('corporate.about', absolute: false)];
            yield ['loc' => route('corporate.contact', absolute: false)];

            foreach (Page::query()->published()->get(['id', 'slug', 'updated_at']) as $record) {
                yield ['loc' => route('corporate.pages.show', $record->slug, absolute: false), 'lastmod' => $record->updated_at];
            }
        });
    }
}
