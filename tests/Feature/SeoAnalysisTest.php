<?php

use App\Models\User;
use App\Modules\Announcements\Filament\Resources\AnnouncementResource\Pages\EditAnnouncement;
use App\Modules\Announcements\Models\Announcement;
use App\Modules\Corporate\Filament\Resources\PageResource\Pages\EditPage;
use App\Modules\Corporate\Models\Page;
use App\Modules\Media\Filament\Resources\ArticleResource\Pages\CreateArticle;
use App\Modules\Media\Filament\Resources\ArticleResource\Pages\EditArticle;
use App\Modules\Media\Filament\Resources\ArticleResource\Pages\ListArticles;
use App\Modules\Media\Models\Article;
use App\Support\Seo\Analysis\SeoAnalyzer;
use App\Support\Seo\Analysis\SeoContent;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;
use App\Support\Seo\Analysis\SeoText;
use App\Support\Seo\Seo;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/** Anahtar kelimenin yerinde geçtiği, bağlantılı ve alt başlıklı ~330 kelimelik metin. */
function seoBody(): string
{
    $paragraph = '<p>'.str_repeat('Kredi çekmek isteyen kişi önce aylık taksiti ve toplam maliyeti hesaplar. ', 9).'</p>';

    return '<p>Konut kredisi, ev almak için bankadan alınan uzun vadeli borçtur.</p>'
        .'<h2>Konut kredisi nasıl hesaplanır?</h2>'.$paragraph
        .'<h2>Faiz oranı neden önemlidir?</h2>'.$paragraph
        .'<p>Ayrıntı için <a href="/kredi-rehberi/">kredi rehberimize</a> ve '
        .'<a href="https://example.org/faiz">resmî faiz tablosuna</a> bakın.</p>'
        .str_repeat($paragraph, 2);
}

function seoResults(SeoSubject $subject): array
{
    return array_map(fn (SeoResult $result) => [$result->status, $result->message], app(SeoAnalyzer::class)->analyze($subject)->results);
}

it('anahtar kelimeyi Türkçe harf, büyük harf, kesme ve eklerden bağımsız eşleştirir', function () {
    expect(SeoText::contains('Konut kredisini nasıl öderim?', 'konut kredisi'))->toBeTrue()
        ->and(SeoText::contains("İSTANBUL'UN İLÇELERİ", 'istanbul ilçeleri'))->toBeTrue()
        ->and(SeoText::contains('Işık ve ılık', 'isik'))->toBeTrue()
        ->and(SeoText::contains('Yarışma', 'arış'))->toBeFalse()
        ->and(SeoText::occurrences('Kredi, kredinin ve krediye', 'kredi'))->toBe(3);
});

it('metni ayrıştırır: kelime, ilk paragraf, alt başlık, görsel ve bağlantılar', function () {
    config(['app.url' => 'https://ornek.com']);
    $content = SeoContent::fromHtml('<p>Bir iki.</p><p>Üç</p><h2>Başlık</h2><img src="a.jpg"><img src="b.jpg" alt="B">'
        .'<a href="/x/">iç</a><a href="https://www.ornek.com/y/">iç</a><a href="https://example.org">dış</a><a href="#not">çapa</a>');

    expect($content)
        ->intro->toBe('Bir iki.')
        ->headings->toBe(['Başlık'])
        ->images->toBe(2)
        ->imagesWithoutAlt->toBe(1)
        ->internalLinks->toBe(2)
        ->externalLinks->toBe(1)
        ->and(count($content->words))->toBe(8);
});

it('iyi hazırlanmış yayın yüksek, sorunlu yayın düşük puan alır; anahtar kelimesiz yayın yeşil olamaz', function () {
    $attributes = [
        'title' => 'Konut Kredisi Nasıl Hesaplanır?',
        'body' => seoBody(),
        'meta_description' => 'Konut kredisi nasıl hesaplanır, faiz oranı toplam maliyeti nasıl değiştirir? Taksit ve vade seçerken dikkat edilecekleri adım adım anlattık.',
    ];
    $good = new Article([...$attributes, 'focus_keyword' => 'konut kredisi']);
    $poor = new Article(['title' => 'Bir yazı', 'body' => '<p>Kısa metin.</p>', 'focus_keyword' => 'konut kredisi']);
    $noKeyword = new Article($attributes);

    expect($good->seoAnalysis()->status())->toBe(SeoResult::GOOD)
        ->and($poor->seoAnalysis()->status())->toBe(SeoResult::BAD)
        ->and($noKeyword->seoAnalysis()->status())->not->toBe(SeoResult::GOOD)
        ->and(seoResults($good->seoSubject()))->toContain(
            [SeoResult::GOOD, 'Anahtar kelime başlıkta geçiyor.'],
            [SeoResult::GOOD, 'Anahtar kelime ilk paragrafta geçiyor.'],
            [SeoResult::GOOD, 'Anahtar kelime adreste (URL) geçiyor.'],
        )
        ->and(seoResults($poor->seoSubject()))->toContain(
            [SeoResult::BAD, 'Anahtar kelime başlıkta geçmiyor. Başlığa ya da meta başlığa ekleyin.'],
            [SeoResult::BAD, 'Metin çok kısa (2 kelime). En az 300 kelime önerilir.'],
        );
});

it('başlığı sayfadaki tam haliyle, adresi yayın rotasından üretir', function () {
    $subject = (new Article(['title' => 'Faiz Oranları']))->seoSubject();

    expect($subject->title)->toBe(app(Seo::class)->pageTitle('Faiz Oranları'))
        ->and($subject->slug)->toBe('faiz-oranlari')
        ->and($subject->url)->toBe(url(route('media.articles.show', 'faiz-oranlari', absolute: false)));
});

it('aynı odak anahtar kelimeyi kullanan başka içerikleri bildirir, kendini saymaz', function () {
    $first = Article::create(['title' => 'Kredi 1', 'focus_keyword' => 'konut kredisi']);
    Article::create(['title' => 'Kredi 2', 'focus_keyword' => 'konut kredisi']);

    expect($first->seoSubject()->keywordUses())->toBe(1)
        ->and((new Article(['focus_keyword' => 'konut kredisi']))->seoSubject()->keywordUses())->toBe(2);
});

it('duyuru kapak görselinin ölçüsünü denetler', function () {
    $subject = fn (int $width, int $height) => (new Announcement(['title' => 'Duyuru']))->seoSubject()->withImage(compact('width', 'height'));

    expect(seoResults($subject(1140, 500)))->toContain([SeoResult::OK, 'Kapak görseli küçük ve oranı uygun değil (1140×500); bulanık görünür ve kenarlarından kırpılır. Önerilen ölçü 1600×900, önemli kısımlar ortada.'])
        ->and(seoResults($subject(1600, 900)))->toContain([SeoResult::GOOD, 'Kapak görseli uygun (1600×900).'])
        ->and(collect(seoResults((new Announcement(['title' => 'Duyuru']))->seoSubject()))->pluck(1)->implode(' '))->not->toContain('Kapak görseli');
});

it('Türkçe okunabilirlik puanını Ateşman formülüyle hesaplar', function () {
    $content = SeoContent::fromHtml('<p>Ali okula gitti. Ayşe eve geldi.</p>');

    // 6 kelime, 2 cümle, 13 hece (ünlü): 198.825 − 40.175 × 13/6 − 2.610 × 6/2
    expect(round($content->readability(), 3))->toBe(round(198.825 - 40.175 * 13 / 6 - 2.610 * 3, 3));
});

describe('panel', function () {
    beforeEach(function () {
        seed(RolesAndPermissionsSeeder::class);
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
        $admin->assignRole('super-admin');
        actingAs($admin);
    });

    it('yayın formu Google önizlemesini ve analizi gösterir, kaydedilmemiş değişikliklerle günceller', function () {
        $article = Article::create(['title' => 'Konut Kredisi', 'body' => '<p>Konut kredisi uzun vadelidir.</p>']);

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->assertSee('Google önizlemesi')
            ->assertSee(app(Seo::class)->pageTitle('Konut Kredisi'))
            ->assertSee('Odak anahtar kelime girilmedi.')
            ->set('data.meta_title', 'Konut Kredisi Hesaplama')
            ->set('data.focus_keyword', 'konut kredisi')
            ->assertSee(app(Seo::class)->pageTitle('Konut Kredisi Hesaplama'))
            ->assertSee('Anahtar kelime başlıkta geçiyor.')
            ->assertSee('Anahtar kelime ilk paragrafta geçiyor.');

        expect($article->fresh()->meta_title)->toBeNull();
    });

    it('odak anahtar kelime kaydedilir', function () {
        $article = Article::create(['title' => 'Konut Kredisi']);

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['focus_keyword' => 'konut kredisi'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($article->fresh()->focus_keyword)->toBe('konut kredisi');
    });

    it('yeni yayın formunda analiz başlıktan üretilecek adresle çalışır', function () {
        Livewire::test(CreateArticle::class)
            ->set('data.title', 'Faiz Oranları')
            ->set('data.focus_keyword', 'faiz oranları')
            ->assertSee('faiz-oranlari')
            ->assertSee('Anahtar kelime adreste (URL) geçiyor.');
    });

    it('listede SEO puanı, sayfa ve duyuru formlarında SEO analizi vardır', function () {
        Article::create(['title' => 'Konut Kredisi']);
        $page = Page::create(['title' => 'Künye', 'body' => '<p>Ekibimiz</p>']);
        $announcement = Announcement::create(['title' => 'Tatil duyurusu']);

        Livewire::test(ListArticles::class)->assertSee('SEO');
        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])->assertSee(app(Seo::class)->pageTitle('Künye'));
        Livewire::test(EditAnnouncement::class, ['record' => $announcement->getRouteKey()])->assertSee('Google önizlemesi');
    });
});
