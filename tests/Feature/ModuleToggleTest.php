<?php

use App\Modules\Media\Filament\Resources\ArticleResource;
use Filament\Facades\Filament;

use function Pest\Laravel\get;

it('loads an enabled module', function () {
    get('/haber')->assertOk();

    expect(Filament::getPanel('admin')->getResources())->toContain(ArticleResource::class);
});

it('does not load a disabled module', function () {
    // Modules are wired while the app boots, so the flag must be set before a fresh boot.
    $_SERVER['MODULE_MEDIA_ENABLED'] = $_ENV['MODULE_MEDIA_ENABLED'] = 'false';

    try {
        $this->refreshApplication();

        get('/haber')->assertNotFound();
        get('/api/haber/articles')->assertNotFound();
        expect(Filament::getPanel('admin')->getResources())->not->toContain(ArticleResource::class);
    } finally {
        unset($_SERVER['MODULE_MEDIA_ENABLED'], $_ENV['MODULE_MEDIA_ENABLED']);
    }
});
