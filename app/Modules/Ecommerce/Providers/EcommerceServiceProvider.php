<?php

namespace App\Modules\Ecommerce\Providers;

use App\Modules\Ecommerce\Models\Product;
use App\Modules\Ecommerce\Services\Payment\IyzicoGateway;
use App\Modules\Ecommerce\Services\Payment\PaymentGateway;
use App\Modules\Ecommerce\Services\Payment\PaymentManager;
use App\Modules\Ecommerce\Services\Payment\StripeGateway;
use App\Support\Modules;
use App\Support\Seo\Sitemap;
use Illuminate\Support\ServiceProvider;

class EcommerceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function ($app) {
            $manager = new PaymentManager;
            $manager->register('stripe', fn () => new StripeGateway(config('services.stripe')));
            $manager->register('iyzico', fn () => new IyzicoGateway(config('services.iyzico')));

            return $manager;
        });

        $this->app->bind(PaymentGateway::class, function ($app) {
            return $app->make(PaymentManager::class)->driver(
                Modules::get('ecommerce')['default_gateway'] ?? 'stripe'
            );
        });
    }

    public function boot(): void
    {
        $this->app->make(Sitemap::class)->add(function () {
            yield ['loc' => route('ecommerce.products.index', absolute: false)];

            foreach (Product::query()->published()->get(['id', 'slug', 'updated_at']) as $record) {
                yield ['loc' => route('ecommerce.products.show', $record->slug, absolute: false), 'lastmod' => $record->updated_at];
            }
        });
    }
}
