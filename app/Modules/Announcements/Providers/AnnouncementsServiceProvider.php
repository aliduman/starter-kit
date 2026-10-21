<?php

namespace App\Modules\Announcements\Providers;

use App\Models\HomepageSection;
use App\Modules\Announcements\Models\Announcement;
use App\Support\Homepage\HomepageBlocks;
use App\Support\ResponseCache\ScheduledCacheBoundaries;
use App\Support\Search\SearchSources;
use App\Support\Seo\Sitemap;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AnnouncementsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Yayın ve bitiş tarihi gelen duyuru cache temizlenince görünür/kaybolur (routes/console.php).
        $this->app->make(ScheduledCacheBoundaries::class)->register(Announcement::class, Announcement::publicationBoundaries());

        $this->app->make(Sitemap::class)->add(function () {
            $records = Announcement::query()->published()->get(['id', 'slug', 'updated_at']);

            if ($records->isNotEmpty()) {
                yield ['loc' => route('announcements.index', absolute: false)];
            }

            foreach ($records as $record) {
                yield ['loc' => $record->url(), 'lastmod' => $record->updated_at];
            }
        });

        $this->app->make(SearchSources::class)->register('announcements', 'Duyurular', fn (string $term, int $limit) => Announcement::query()
            ->published()
            ->whereAny(['title', 'summary', 'body'], 'like', "%{$term}%")
            ->latest('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (Announcement $announcement) => [
                'title' => $announcement->title,
                'url' => $announcement->url(),
                'excerpt' => Str::limit(strip_tags($announcement->summary ?: (string) $announcement->body), 160),
            ]));

        $this->app->make(HomepageBlocks::class)->register(
            'announcements',
            'Duyurular',
            fn (HomepageSection $section) => [
                'announcements' => Announcement::query()
                    ->current()
                    ->latest('published_at')
                    ->limit((int) $section->setting('limit', 3))
                    ->get()
                    ->map(fn (Announcement $announcement) => $announcement->toCard()),
                'url' => route('announcements.index', absolute: false),
            ],
            fn () => [TextInput::make('settings.limit')->label('Gösterilecek duyuru sayısı')->numeric()->minValue(1)->maxValue(12)->default(3)],
        );
    }
}
