<?php

use App\Models\HomepageSection;
use App\Models\MenuItem;
use App\Modules\Announcements\Models\Announcement;
use App\Support\Homepage\HomepageBlocks;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;
use function Pest\Laravel\seed;

it('installs the default menus and homepage sections once', function () {
    seed(DatabaseSeeder::class);
    seed(DatabaseSeeder::class);

    // Beklenen öğeler sitenin config/menus.php'sinden; rotası olmayan (modülü kapalı) öğe atlanır.
    $main = collect(config('menus.defaults.main'))
        ->filter(fn (array $item) => ! isset($item['route']) || Route::has($item['route']))
        ->values();
    $blocks = array_keys(array_filter(app(HomepageBlocks::class)->all(), fn (array $block) => $block['default']));

    expect(MenuItem::query()->where('location', 'main')->orderBy('sort_order')->pluck('title')->all())
        ->toBe($main->pluck('title')->all())
        ->and(HomepageSection::query()->orderBy('sort_order')->pluck('key')->all())
        ->toBe($blocks);

    $routed = $main->search(fn (array $item) => isset($item['route']));

    get('/')->assertInertia(fn ($page) => $page
        ->has('menus.main', $main->count())
        ->where("menus.main.{$routed}.href", route($main[$routed]['route'], absolute: false)));
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
