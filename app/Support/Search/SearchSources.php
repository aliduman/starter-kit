<?php

namespace App\Support\Search;

use Closure;

/**
 * Site araması (/arama). Modüller aranabilir içeriklerini provider'larında kaydeder; arama sayfası sonuçları kaynak
 * sırasıyla gruplar. Paylaşımlı hostta arama motoru servisi yoktur: kaynaklar veritabanında (LIKE / FULLTEXT) arar.
 */
final class SearchSources
{
    /** @var array<string, array{label: string, search: Closure}> */
    private array $sources = [];

    /**
     * Arama closure'ı yalnızca yayındaki kayıtları döndürür: fn (string $term, int $limit) => iterable<array{title,
     * url, excerpt?}>.
     */
    public function register(string $key, string $label, Closure $search): static
    {
        $this->sources[$key] = ['label' => $label, 'search' => $search];

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->sources === [];
    }

    /** @return list<array{key: string, label: string, results: list<array{title: string, url: string, excerpt: ?string}>}> */
    public function search(string $term, int $limit = 10): array
    {
        $groups = [];

        foreach ($this->sources as $key => $source) {
            $results = [];

            foreach (($source['search'])($term, $limit) as $hit) {
                $results[] = ['title' => $hit['title'], 'url' => $hit['url'], 'excerpt' => $hit['excerpt'] ?? null];
            }

            if ($results !== []) {
                $groups[] = ['key' => $key, 'label' => $source['label'], 'results' => $results];
            }
        }

        return $groups;
    }
}
