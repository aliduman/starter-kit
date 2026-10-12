<?php

namespace App\Modules\RealEstate\Providers;

use App\Modules\RealEstate\Models\Listing;
use App\Support\Seo\Sitemap;
use Illuminate\Support\ServiceProvider;

class RealEstateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->make(Sitemap::class)->add(function () {
            yield ['loc' => route('realestate.listings.index', absolute: false)];

            foreach (Listing::query()->published()->get(['id', 'slug', 'updated_at']) as $record) {
                yield ['loc' => route('realestate.listings.show', $record->slug, absolute: false), 'lastmod' => $record->updated_at];
            }
        });
    }
}
