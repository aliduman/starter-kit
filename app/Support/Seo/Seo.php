<?php

namespace App\Support\Seo;

use App\Support\Routing\LengthAwarePaginator;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Inertia\AlwaysProp;
use Inertia\Inertia;

/**
 * Sayfa başına SEO etiketleri (title, description, canonical, robots, Open Graph, Twitter, JSON-LD).
 *
 * Etiketler Inertia `head` prop'u olarak gider: ilk yüklemede root Blade view'ı basar (WhatsApp/Facebook/X
 * önizlemeleri JS çalıştırmaz), istemcide Inertia head manager (`serverHead: true`) aynı listeyi gezinmede
 * günceller. Her etiketin `data-inertia` anahtarı vardır. Başlığın tek kaynağı burasıdır: Vue sayfalarındaki
 * <Head> başlığı sunucu başlığını ezer, kullanılmaz.
 *
 * Site düzeyindeki varsayılanları (ad, açıklama, görsel, sosyal hesaplar, arama/RSS adresleri) modüller
 * resolveDefaultsUsing() ile verir; çekirdek hiçbir modüle bağlı değildir.
 */
class Seo
{
    private const DESCRIPTION_LIMIT = 160;

    private const HOME_LABEL = 'Ana Sayfa';

    /** @var list<callable(): array<string, mixed>> */
    private array $defaultsResolvers = [];

    /**
     * Sayfa verisinden `head` prop'u; kısmi yüklemelerde (only) de gönderilir.
     *
     * `breadcrumbs` verilirse görünen gezinti izi (paylaşılan `breadcrumbs` prop'u) ve JSON-LD BreadcrumbList
     * aynı listeden üretilir; ikisi birbirinden ayrışamaz.
     */
    public static function page(array $data = []): AlwaysProp
    {
        if (isset($data['breadcrumbs'])) {
            Inertia::share('breadcrumbs', self::breadcrumbs($data['breadcrumbs']));
        }

        return Inertia::always(fn () => app(self::class)->tags($data));
    }

    /**
     * Gezinti izi: ana sayfa başa eklenir, son öğe geçerli sayfadır ve bağlantı taşımaz.
     *
     * @param  list<array{0: string, 1?: ?string}>  $trail  Ana sayfa hariç [başlık, adres] çiftleri.
     * @return list<array{label: string, href: ?string}>
     */
    public static function breadcrumbs(array $trail): array
    {
        $crumbs = [['label' => self::HOME_LABEL, 'href' => '/'], ...array_map(
            fn (array $crumb) => ['label' => $crumb[0], 'href' => $crumb[1] ?? null],
            $trail,
        )];
        $crumbs[array_key_last($crumbs)]['href'] = null;

        return $crumbs;
    }

    /**
     * Site varsayılanlarını ekler. Anahtarlar: site_name, home_title ve description (ana sayfanın başlığı ve
     * açıklaması), image, same_as (list), search_url
     * ({search_term_string} içeren), feeds (list<[title, href]>), verification (meta adı => doğrulama kodu; ör.
     * google-site-verification), analytics (sağlayıcı => kimlik; ör. ga4, istemciye bkz. analytics()).
     *
     * @param  callable(): array<string, mixed>  $resolver
     */
    public function resolveDefaultsUsing(callable $resolver): static
    {
        $this->defaultsResolvers[] = $resolver;

        return $this;
    }

    /** @return array<string, mixed> */
    public function defaults(): array
    {
        $defaults = [
            'site_name' => config('app.name'),
            'home_title' => config('seo.home_title'),
            'description' => config('seo.description'),
            'image' => config('seo.image'),
            'same_as' => [],
            'search_url' => null,
            'feeds' => [],
            'verification' => array_filter((array) config('seo.verification')),
            'analytics' => array_filter((array) config('seo.analytics')),
        ];

        foreach ($this->defaultsResolvers as $resolver) {
            $defaults = [...$defaults, ...array_filter($resolver(), fn ($value) => filled($value))];
        }

        return $defaults;
    }

    /**
     * Sayfa verisi: title (site adı olmadan), description, image, type (website|article|profile),
     * url (canonical), published_time, modified_time, author, section, noindex, schema (JSON-LD düğümleri),
     * breadcrumbs (gezinti izi; bkz. breadcrumbs()), canonical_query (canonical'da korunacak sorgu parametreleri;
     * ör. ayrı içerik gösteren `tur` sekmesi).
     *
     * @return list<string>
     */
    public function tags(array $data = []): array
    {
        $site = $this->defaults();
        $siteName = (string) $site['site_name'];
        $home = request()->getPathInfo() === '/';
        $title = $this->fullTitle($data['title'] ?? ($home ? $site['home_title'] : null), $siteName);
        // Site açıklaması yalnızca ana sayfanın: her sayfada tekrarlanırsa yinelenen açıklama olur. Açıklaması
        // olmayan sayfada etiket basılmaz, arama motoru özeti sayfa metninden çıkarır.
        $description = $this->plain($data['description'] ?? null) ?: ($home ? $this->plain($site['description']) : null);
        $image = $this->absolute($data['image'] ?? null) ?: $this->absolute($site['image']);
        $url = $data['url'] ?? $this->canonical($data['canonical_query'] ?? []);
        $type = $data['type'] ?? 'website';
        $robots = ! config('seo.indexable') || ($data['noindex'] ?? false) ? 'noindex, nofollow' : config('seo.robots');

        $tags = [
            '<title data-inertia="">'.e($title).'</title>',
            $this->meta('robots', 'name', 'robots', $robots),
            $this->link('canonical', 'canonical', $url),
            $this->meta('description', 'name', 'description', $description),
            $this->meta('og:site_name', 'property', 'og:site_name', $siteName),
            $this->meta('og:locale', 'property', 'og:locale', config('seo.locale')),
            $this->meta('og:type', 'property', 'og:type', $type),
            $this->meta('og:title', 'property', 'og:title', $title),
            $this->meta('og:description', 'property', 'og:description', $description),
            $this->meta('og:url', 'property', 'og:url', $url),
            $this->meta('og:image', 'property', 'og:image', $image),
            $this->meta('twitter:card', 'name', 'twitter:card', $image ? 'summary_large_image' : 'summary'),
            $this->meta('twitter:site', 'name', 'twitter:site', config('seo.twitter_site')),
            $this->meta('article:published_time', 'property', 'article:published_time', $this->iso($data['published_time'] ?? null)),
            $this->meta('article:modified_time', 'property', 'article:modified_time', $this->iso($data['modified_time'] ?? null)),
            $this->meta('article:author', 'property', 'article:author', $data['author'] ?? null),
            $this->meta('article:section', 'property', 'article:section', $data['section'] ?? null),
        ];

        foreach ($site['verification'] as $name => $token) {
            $tags[] = $this->meta('verification:'.$name, 'name', $name, $token);
        }

        foreach (array_values($site['feeds']) as $i => [$feedTitle, $href]) {
            $tags[] = '<link data-inertia="feed-'.$i.'" rel="alternate" type="application/rss+xml" title="'
                .e($feedTitle).'" href="'.e($this->absolute($href)).'">';
        }

        $nodes = $data['schema'] ?? [];

        if (isset($data['breadcrumbs'])) {
            $nodes[] = $this->breadcrumbList($data['breadcrumbs'], $url);
        }

        $tags[] = $this->schema($site, $nodes);

        return array_values(array_filter($tags));
    }

    /**
     * İstemcide çerez onayından sonra yüklenecek ölçüm araçları (sağlayıcı => kimlik). Dizine kapalı ortamlar
     * (test/staging) ölçülmez; canlı verisi kirlenmesin.
     *
     * @return array<string, string>
     */
    public function analytics(): array
    {
        return config('seo.indexable') ? $this->defaults()['analytics'] : [];
    }

    /** Yayın/kuruluş düğümleri her sayfada; sayfanın kendi düğümleri (Article, ProfilePage...) eklenir. */
    private function schema(array $site, array $nodes): string
    {
        $home = url('/');

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => $home.'#organization',
            'name' => $site['site_name'],
            'url' => $home,
            'logo' => $this->absolute($site['image']),
            'sameAs' => array_values($site['same_as']) ?: null,
        ]);

        $website = array_filter([
            '@type' => 'WebSite',
            '@id' => $home.'#website',
            'name' => $site['site_name'],
            'url' => $home,
            'inLanguage' => str_replace('_', '-', (string) config('seo.locale')),
            'publisher' => ['@id' => $home.'#organization'],
            'potentialAction' => $site['search_url'] ? [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => $this->absolute($site['search_url'])],
                'query-input' => 'required name=search_term_string',
            ] : null,
        ]);

        $json = json_encode(
            ['@context' => 'https://schema.org', '@graph' => [$organization, $website, ...$nodes]],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP,
        );

        return '<script data-inertia="schema" type="application/ld+json">'.$json.'</script>';
    }

    /** Görünen izle aynı öğeler; geçerli sayfanın adresi canonical'dır. */
    private function breadcrumbList(array $trail, string $url): array
    {
        $crumbs = self::breadcrumbs($trail);

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $crumb, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['label'],
                'item' => $crumb['href'] === null ? $url : $this->absolute($crumb['href']),
            ], $crumbs, array_keys($crumbs)),
        ];
    }

    /** Organizasyon düğümüne referans (Article.publisher vb. için). */
    public static function organizationRef(): array
    {
        return ['@id' => url('/').'#organization'];
    }

    private function fullTitle(?string $title, string $siteName): string
    {
        $title = trim((string) $title);

        if ($title === '') {
            return $siteName;
        }

        return Str::contains($title, $siteName) ? $title : $title.config('seo.title_separator', ' - ').$siteName;
    }

    /**
     * Sayfalı listede ?page ve sayfanın bildirdiği parametreler korunur; süzgeç/izleme parametreleri canonical'a
     * girmez. Sayfalama göstermeyen sayfada ?page yok sayılır (eski /page/2/ adresleri ?page=2'ye yönlenir).
     *
     * @param  list<string>  $keep
     */
    private function canonical(array $keep = []): string
    {
        $request = request();
        $page = $request->attributes->get(LengthAwarePaginator::REQUEST_ATTRIBUTE) ? (int) $request->query('page', 1) : 1;
        $query = http_build_query(array_filter([
            ...array_map(fn (string $key) => $request->query($key), array_combine($keep, $keep)),
            'page' => $page > 1 ? $page : null,
        ], fn ($value) => is_string($value) || is_int($value)));

        // url()->current() sondaki "/" işaretini korur (Request::url() siler); bkz. TrailingSlash.
        return url()->current().($query === '' ? '' : '?'.$query);
    }

    private function plain(?string $text): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5)));

        return $text === '' ? null : Str::limit($text, self::DESCRIPTION_LIMIT, '…', preserveWords: true);
    }

    private function absolute(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        return Str::startsWith($url, ['http://', 'https://', '//']) ? $url : url($url);
    }

    private function iso(mixed $time): ?string
    {
        return $time instanceof CarbonInterface ? $time->toAtomString() : null;
    }

    private function meta(string $key, string $attribute, string $name, ?string $content): ?string
    {
        if (blank($content)) {
            return null;
        }

        return '<meta data-inertia="'.e($key).'" '.$attribute.'="'.e($name).'" content="'.e($content).'">';
    }

    private function link(string $key, string $rel, ?string $href): ?string
    {
        return blank($href) ? null : '<link data-inertia="'.e($key).'" rel="'.e($rel).'" href="'.e($href).'">';
    }
}
