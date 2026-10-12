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

    get('/')->assertSee('content="index, follow"', false);
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
