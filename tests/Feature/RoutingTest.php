<?php

use App\Support\Routing\ContentPaths;
use App\Support\Routing\LegacyRedirects;

use function Pest\Laravel\get;

it('serves root content paths from module resolvers after static routes', function () {
    app(ContentPaths::class)->register(fn (string $path) => in_array($path, ['merhaba/dunya', 'about'], true)
        ? fn () => response('kök içerik: '.$path)
        : null);

    get('/merhaba/dunya')->assertOk()->assertSee('kök içerik: merhaba/dunya');
    get(route('corporate.about', absolute: false))->assertOk()->assertInertia(fn ($page) => $page->component('Corporate/About'));
    get('/bilinmeyen')->assertNotFound();
});

it('never treats the admin panel or system paths as content', function () {
    app(ContentPaths::class)->register(fn () => fn () => response('içerik'));
    app(LegacyRedirects::class)->add('~.*~', fn () => '/yonlendirildi');

    get('/admin/login')->assertOk()->assertDontSee('içerik');
    get('/api/haber/articles')->assertOk();
});

it('301s registered legacy URL shapes to their new address', function () {
    app(LegacyRedirects::class)->add('~^/eski-haber/([\w-]+)$~', fn (array $m) => LegacyRedirects::path('haber/'.$m[1]));

    get('/eski-haber/deneme?utm=x')->assertStatus(301)->assertRedirect('/haber/deneme');
});

it('serves module pages without a trailing slash by default', function () {
    expect(route('media.articles.index'))->toBe(url('/haber'));

    get('/haber/')->assertStatus(301)->assertRedirect(url('/haber'));
    get('/haber?page=2')->assertOk();
});

it('serves module pages with a trailing slash when the rule is on', function () {
    config(['seo.trailing_slash' => true]);

    expect(route('media.articles.index'))->toBe(url('/haber').'/');

    get('/haber?page=2')->assertStatus(301)->assertRedirect(url('/haber').'/?page=2');
    get('/haber/')
        ->assertOk()
        ->assertSee('<link data-inertia="canonical" rel="canonical" href="'.url('/haber').'/">', false);
});
