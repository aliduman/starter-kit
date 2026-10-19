<?php

use App\Modules\Corporate\Models\Page;
use App\Modules\Ecommerce\Models\Product;
use App\Modules\Media\Models\Article;

use function Pest\Laravel\get;

function publishedArticle(array $attributes = []): Article
{
    return Article::create([
        'title' => 'Deneme Haberi',
        'excerpt' => 'Kısa <b>özet</b> metni.',
        'body' => '<p>Gövde</p>',
        'is_published' => true,
        'published_at' => now()->subHour(),
        ...$attributes,
    ]);
}

it('prints the SEO tags of a content page in the first HTML response', function () {
    $article = publishedArticle();

    get(route('media.articles.show', $article->slug))
        ->assertOk()
        ->assertSee('<title data-inertia="">Deneme Haberi · Starter Kit</title>', false)
        ->assertSee('<meta data-inertia="description" name="description" content="Kısa özet metni.">', false)
        ->assertSee('<meta data-inertia="og:type" property="og:type" content="article">', false)
        ->assertSee('<link data-inertia="canonical" rel="canonical" href="'.route('media.articles.show', $article->slug).'">', false)
        ->assertSee('"@type":"Article","headline":"Deneme Haberi"', false);
});

it('sends the head prop on every page, with site defaults where the page sets nothing', function () {
    get('/')->assertInertia(fn ($page) => $page->has('head'));

    get('/')->assertSee('<title data-inertia="">Starter Kit</title>', false);
});

it('uses the home title and site description on the homepage only', function () {
    config(['seo.home_title' => 'Starter Kit · Modüler site altyapısı', 'seo.description' => 'Site açıklaması.']);

    get('/')
        ->assertSee('<title data-inertia="">Starter Kit · Modüler site altyapısı</title>', false)
        ->assertSee('<meta data-inertia="description" name="description" content="Site açıklaması.">', false);

    get(route('media.articles.index'))
        ->assertSee('<title data-inertia="">Haberler · Starter Kit</title>', false)
        ->assertDontSee('name="description"', false);
});

it('hides drafts and scheduled content from public pages', function () {
    $draft = publishedArticle(['title' => 'Taslak', 'is_published' => false]);
    $scheduled = publishedArticle(['title' => 'Planlı', 'published_at' => now()->addDay()]);

    get(route('media.articles.show', $draft->slug))->assertNotFound();
    get(route('media.articles.show', $scheduled->slug))->assertNotFound();
});

it('prints noindex and blocks robots.txt when the site is not indexable', function () {
    config(['seo.indexable' => false]);

    get('/')->assertSee('content="noindex, nofollow"', false);
    get('/robots.txt')->assertOk()->assertSeeText("User-agent: *\nDisallow: /\n", false);
});

it('prints index and lists the sitemap in robots.txt when indexable', function () {
    config(['seo.indexable' => true]);

    get('/')->assertSee('content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1"', false);
    get('/robots.txt')->assertSee('Disallow: /admin')->assertSee('Sitemap: '.route('sitemap'));
});

it('lists the published content of every enabled module in the sitemap', function () {
    $article = publishedArticle();
    $draft = publishedArticle(['title' => 'Taslak', 'is_published' => false]);
    $page = Page::create(['title' => 'Künye', 'is_published' => true]);
    $product = Product::create(['name' => 'Kitap', 'price' => 10, 'is_active' => true]);

    get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('<loc>'.url('/').'</loc>', false)
        ->assertSee(route('media.articles.show', $article->slug))
        ->assertSee(route('corporate.pages.show', $page->slug))
        ->assertSee(route('ecommerce.products.show', $product->slug))
        ->assertDontSee(route('media.articles.show', $draft->slug));
});

it('keeps empty listing pages out of the index and the sitemap until they have content', function () {
    config(['seo.indexable' => true]);

    get(route('media.articles.index'))->assertSee('content="noindex, nofollow"', false);
    get('/sitemap.xml')->assertDontSee('<loc>'.route('media.articles.index').'</loc>', false);

    publishedArticle();

    get(route('media.articles.index'))->assertSee('content="index, follow', false);
    get('/sitemap.xml')->assertSee('<loc>'.route('media.articles.index').'</loc>', false);
});

it('redirects other schemes and hosts to the same path on APP_URL', function () {
    config(['app.url' => 'https://site.test', 'seo.redirect_to_app_url' => true]);

    get('http://site.test/haber?a=1')->assertStatus(301)->assertRedirect('https://site.test/haber?a=1');
    get('https://www.site.test/')->assertStatus(301)->assertRedirect('https://site.test/');
    get('https://site.test/')->assertOk();

    config(['seo.redirect_to_app_url' => false]);
    get('http://www.site.test/')->assertOk();
});

it('prints search engine verification meta tags from config', function () {
    config()->set('seo.verification', ['google-site-verification' => 'abc_DEF-123', 'msvalidate.01' => null]);

    get('/')
        ->assertSee('<meta data-inertia="verification:google-site-verification" name="google-site-verification" content="abc_DEF-123">', false)
        ->assertDontSee('msvalidate.01', false);
});

it('sends analytics trackers to the client only when the site is indexable', function () {
    config()->set('seo.analytics', ['ga4' => 'G-AB12CD34']);

    config()->set('seo.indexable', true);
    get('/')->assertInertia(fn ($page) => $page->where('analytics', ['ga4' => 'G-AB12CD34']));

    config()->set('seo.indexable', false);
    get('/')->assertInertia(fn ($page) => $page->where('analytics', []));
});
