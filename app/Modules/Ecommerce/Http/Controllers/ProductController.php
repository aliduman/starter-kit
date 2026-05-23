<?php

namespace App\Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ecommerce\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Ecommerce/Index', [
            'products' => Product::query()->where('is_active', true)->paginate(24),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = Product::query()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Ecommerce/Show', [
            'product' => $product,
        ]);
    }

    public function apiIndex()
    {
        return Product::query()->where('is_active', true)->paginate(24);
    }

    public function apiShow(string $slug)
    {
        return Product::query()->where('slug', $slug)->firstOrFail();
    }
}
