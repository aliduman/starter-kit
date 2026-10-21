<?php

namespace App\Modules\Media\Providers;

use App\Models\HomepageSection;
use App\Modules\Media\Models\Article;
use App\Support\Homepage\HomepageBlocks;
use App\Support\ResponseCache\ScheduledCacheBoundaries;
use App\Support\Search\SearchSources;
use App\Support\Seo\Sitemap;
use App\Support\Taxonomy\CategoryTypes;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

        $this->app->make(CategoryTypes::class)->register(Article::CATEGORY_TYPE, 'Haberler');

        $this->app->make(SearchSources::class)->register('articles', 'Haberler', fn (string $term, int $limit) => Article::query()
            ->published()
            ->whereAny(['title', 'excerpt', 'body'], 'like', "%{$term}%")
            ->latest('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (Article $article) => [
                'title' => $article->title,
                'url' => route('media.articles.show', $article->slug, absolute: false),
                'excerpt' => Str::limit(strip_tags($article->excerpt ?: (string) $article->body), 160),
            ]));

        $this->app->make(HomepageBlocks::class)->register(
            'latest_articles',
            'Son Haberler',
            fn (HomepageSection $section) => [
                'articles' => Article::query()
                    ->published()
                    ->latest('published_at')
                    ->limit((int) $section->setting('limit', 3))
                    ->get(['id', 'title', 'slug', 'excerpt', 'published_at']),
                'url' => route('media.articles.index', absolute: false),
            ],
            fn () => [TextInput::make('settings.limit')->label('Gösterilecek haber sayısı')->numeric()->minValue(1)->maxValue(12)->default(3)],
        );
    }
}
