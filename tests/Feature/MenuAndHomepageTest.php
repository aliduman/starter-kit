<?php

use App\Models\HomepageSection;
use App\Models\MenuItem;
use App\Modules\Announcements\Models\Announcement;
use Database\Seeders\DatabaseSeeder;

use function Pest\Laravel\get;
use function Pest\Laravel\seed;

it('installs the default menus and homepage sections once', function () {
    seed(DatabaseSeeder::class);
    seed(DatabaseSeeder::class);

    expect(MenuItem::query()->where('location', 'main')->pluck('title')->all())
        ->toBe(['Anasayfa', 'Hakkımızda', 'Mağaza', 'Emlak', 'Haberler', 'Duyurular'])
        ->and(HomepageSection::query()->orderBy('sort_order')->pluck('key')->all())
        ->toBe(['latest_articles', 'announcements']);

    get('/')->assertInertia(fn ($page) => $page
        ->has('menus.main', 6)
        ->where('menus.main.1.href', '/about')
        ->where('menus.footer.0.title', 'İletişim'));
});

it('renders active homepage sections in panel order with their data', function () {
    Announcement::create(['title' => 'Yeni dönem', 'is_published' => true, 'published_at' => now()->subDay()]);
    HomepageSection::create(['key' => 'announcements', 'title' => 'Haberdar olun', 'sort_order' => 1]);
    HomepageSection::create(['key' => 'latest_articles', 'sort_order' => 2, 'is_active' => false]);
    HomepageSection::create(['key' => 'unknown_block', 'sort_order' => 3]);

    get('/')->assertInertia(fn ($page) => $page
        ->has('sections', 1)
        ->where('sections.0.component', 'Announcements')
        ->where('sections.0.title', 'Haberdar olun')
        ->where('sections.0.data.announcements.0.title', 'Yeni dönem'));
});

it('drops the sections of a disabled module', function () {
    $_SERVER['MODULE_ANNOUNCEMENTS_ENABLED'] = $_ENV['MODULE_ANNOUNCEMENTS_ENABLED'] = 'false';

    try {
        $this->refreshApplication();
        $this->artisan('migrate:fresh');

        HomepageSection::create(['key' => 'announcements', 'sort_order' => 1]);
        HomepageSection::create(['key' => 'latest_articles', 'sort_order' => 2]);

        get('/')->assertInertia(fn ($page) => $page
            ->has('sections', 1)
            ->where('sections.0.key', 'latest_articles'));
    } finally {
        unset($_SERVER['MODULE_ANNOUNCEMENTS_ENABLED'], $_ENV['MODULE_ANNOUNCEMENTS_ENABLED']);
    }
});
