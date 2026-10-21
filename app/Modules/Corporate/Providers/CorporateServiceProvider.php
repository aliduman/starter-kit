<?php

namespace App\Modules\Corporate\Providers;

use App\Modules\Corporate\Models\Page;
use App\Support\ResponseCache\ScheduledCacheBoundaries;
use App\Support\Search\SearchSources;
use App\Support\Seo\Sitemap;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

        $this->app->make(SearchSources::class)->register('pages', 'Sayfalar', fn (string $term, int $limit) => Page::query()
            ->published()
            ->whereAny(['title', 'excerpt', 'body'], 'like', "%{$term}%")
            ->limit($limit)
            ->get()
            ->map(fn (Page $page) => [
                'title' => $page->title,
                'url' => route('corporate.pages.show', $page->slug, absolute: false),
                'excerpt' => Str::limit(strip_tags($page->excerpt ?: (string) $page->body), 160),
            ]));
    }
}
