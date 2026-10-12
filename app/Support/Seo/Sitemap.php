<?php

namespace App\Support\Seo;

use Carbon\CarbonInterface;

/**
 * /sitemap.xml kaynakları. Her modül kendi provider'ında add() ile adres üreten bir kaynak bildirir; pasif
 * modülün kaynağı hiç kaydedilmez.
 */
class Sitemap
{
    /** @var list<callable(): iterable<array{loc: string, lastmod?: ?CarbonInterface}>> */
    private array $sources = [];

    /** @param  callable(): iterable<array{loc: string, lastmod?: ?CarbonInterface}>  $source */
    public function add(callable $source): static
    {
        $this->sources[] = $source;

        return $this;
    }

    /** @return iterable<array{loc: string, lastmod: ?string}> */
    public function entries(): iterable
    {
        $seen = [];

        foreach ($this->sources as $source) {
            foreach ($source() as $entry) {
                $loc = url($entry['loc']);

                if (isset($seen[$loc])) {
                    continue;
                }

                $seen[$loc] = true;

                yield [
                    'loc' => $loc,
                    'lastmod' => ($entry['lastmod'] ?? null) instanceof CarbonInterface ? $entry['lastmod']->toAtomString() : null,
                ];
            }
        }
    }
}
