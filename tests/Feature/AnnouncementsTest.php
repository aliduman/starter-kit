<?php

use App\Models\HomepageSection;
use App\Modules\Announcements\Models\Announcement;

use function Pest\Laravel\get;

it('lists published announcements, including ended ones, newest first', function () {
    Announcement::create(['title' => 'Eski duyuru', 'is_published' => true, 'published_at' => now()->subWeek(), 'ends_at' => now()->subDay()]);
    Announcement::create(['title' => 'Yeni duyuru', 'is_published' => true, 'published_at' => now()->subHour()]);
    Announcement::create(['title' => 'Taslak', 'is_published' => false]);
    Announcement::create(['title' => 'Planlı', 'is_published' => true, 'published_at' => now()->addDay()]);

    get('/duyurular')->assertOk()->assertInertia(fn ($page) => $page
        ->component('Announcements/Index')
        ->has('announcements.data', 2)
        ->where('announcements.data.0.title', 'Yeni duyuru'));
});

it('shows a published announcement and hides drafts', function () {
    $published = Announcement::create(['title' => 'Program değişikliği', 'body' => '<p>Ayrıntı</p>', 'is_published' => true]);
    $draft = Announcement::create(['title' => 'Gizli', 'is_published' => false]);

    get("/duyurular/{$published->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('announcement.body', '<p>Ayrıntı</p>'))
        ->assertSee('<title data-inertia="">Program değişikliği · Starter Kit</title>', false);

    get("/duyurular/{$draft->slug}")->assertNotFound();
});

it('keeps ended announcements off the homepage', function () {
    Announcement::create(['title' => 'Bitti', 'is_published' => true, 'ends_at' => now()->subMinute()]);
    Announcement::create(['title' => 'Sürüyor', 'is_published' => true, 'ends_at' => now()->addDay()]);
    HomepageSection::create(['key' => 'announcements']);

    get('/')->assertInertia(fn ($page) => $page
        ->has('sections.0.data.announcements', 1)
        ->where('sections.0.data.announcements.0.title', 'Sürüyor'));
});

it('adds published announcements to the sitemap', function () {
    $announcement = Announcement::create(['title' => 'Haritada', 'is_published' => true]);

    get('/sitemap.xml')
        ->assertSee(route('announcements.index'))
        ->assertSee(route('announcements.show', $announcement->slug));
});
