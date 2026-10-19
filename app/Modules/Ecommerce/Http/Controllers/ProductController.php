<?php

namespace App\Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ecommerce\Models\Product;
use App\Support\Seo\Seo;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()->published()->paginate(24);

        return Inertia::render('Ecommerce/Index', [
            'products' => $products,
            // Boş liste ince içerik: dizine girmesin (sitemap'te de yok).
            'head' => Seo::page(['title' => 'Ürünler', 'noindex' => $products->isEmpty()]),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = Product::query()->published()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Ecommerce/Show', [
            'product' => $product,
            'head' => Seo::page([
                'title' => $product->name,
                'description' => $product->description,
                'modified_time' => $product->updated_at,
            ]),
        ]);
    }

    public function apiIndex()
    {
        return Product::query()->published()->paginate(24);
    }

    public function apiShow(string $slug)
    {
        return Product::query()->published()->where('slug', $slug)->firstOrFail();
    }
}
