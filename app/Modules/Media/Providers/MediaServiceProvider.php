<?php

namespace App\Modules\Media\Providers;

use App\Modules\Media\Models\Article;
use App\Support\ResponseCache\ScheduledCacheBoundaries;
use App\Support\Seo\Sitemap;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Yayın tarihi gelen kayıt cache temizlenince görünür (routes/console.php).
        $this->app->make(ScheduledCacheBoundaries::class)->register(Article::class, Article::publicationBoundaries());

        $this->app->make(Sitemap::class)->add(function () {
            $records = Article::query()->published()->get(['id', 'slug', 'updated_at']);

            // Boş liste sayfası ince içeriktir: sitemap'e girmez, sayfası da noindex.
            if ($records->isNotEmpty()) {
                yield ['loc' => route('media.articles.index', absolute: false)];
            }

            foreach ($records as $record) {
                yield ['loc' => route('media.articles.show', $record->slug, absolute: false), 'lastmod' => $record->updated_at];
            }
        });
    }
}
