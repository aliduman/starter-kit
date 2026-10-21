<?php

use App\Modules\Announcements\Models\Announcement;
use App\Modules\Corporate\Models\Page;
use App\Modules\Media\Models\Article;

use function Pest\Laravel\get;

it('groups published matches from every registered source', function () {
    Page::create(['title' => 'Radyo hakkında', 'body' => '<p>Kuruluş</p>', 'is_published' => true]);
    Article::create(['title' => 'Radyo yayını başladı', 'is_published' => true, 'published_at' => now()->subHour()]);
    Article::create(['title' => 'Radyo taslağı', 'is_published' => false]);
    Announcement::create(['title' => 'Yeni radyo programı', 'summary' => '<b>Özet</b>', 'is_published' => true]);

    get('/arama?q=radyo')->assertOk()->assertInertia(fn ($page) => $page
        ->component('Search')
        ->has('groups', 3)
        ->where('groups.0.label', 'Sayfalar')
        ->where('groups.1.results', fn ($results) => count($results) === 1 && $results[0]['title'] === 'Radyo yayını başladı')
        ->where('groups.2.results.0.excerpt', 'Özet'));
});

it('ignores too short queries and keeps result pages out of the index', function () {
    Page::create(['title' => 'A sayfası', 'is_published' => true]);

    get('/arama?q=a')
        ->assertInertia(fn ($page) => $page->where('groups', []))
        ->assertSee('content="noindex, nofollow"', false);
});

it('advertises the search page in the site schema', function () {
    get('/')->assertSee('"urlTemplate":"http://localhost/arama?q={search_term_string}"', false);
});
