<?php

use App\Modules\Media\Models\Article;
use App\Support\ResponseCache\ScheduledCacheBoundaries;
use Spatie\ResponseCache\Facades\ResponseCache;

beforeEach(fn () => config(['responsecache.enabled' => true]));

it('clears the response cache when public content changes', function () {
    ResponseCache::shouldReceive('clear')->twice();

    $article = Article::create(['title' => 'Haber', 'is_published' => true]);
    $article->delete();
});

it('clears the response cache once a scheduled publish time passes', function () {
    config(['responsecache.enabled' => false]);
    Article::create(['title' => 'Planlı', 'is_published' => true, 'published_at' => now()->addMinutes(5)]);
    config(['responsecache.enabled' => true]);
    ResponseCache::shouldReceive('clear')->never();

    $boundaries = app(ScheduledCacheBoundaries::class);

    expect($boundaries->purgeIfCrossed())->toBeFalse();

    ResponseCache::shouldReceive('clear')->once();
    $this->travel(10)->minutes();

    expect($boundaries->purgeIfCrossed())->toBeTrue()
        ->and($boundaries->purgeIfCrossed())->toBeFalse();
});
