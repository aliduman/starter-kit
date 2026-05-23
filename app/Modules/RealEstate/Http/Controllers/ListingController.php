<?php

namespace App\Modules\RealEstate\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RealEstate\Models\Listing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    public function index(Request $request): Response
    {
        $listings = Listing::query()
            ->when($request->string('type')->toString(), fn ($q, $type) => $q->where('type', $type))
            ->when($request->string('city')->toString(), fn ($q, $city) => $q->where('city', $city))
            ->where('is_published', true)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('RealEstate/Index', [
            'listings' => $listings,
            'filters' => $request->only('type', 'city'),
        ]);
    }

    public function show(string $slug): Response
    {
        $listing = Listing::query()->where('slug', $slug)->firstOrFail();

        return Inertia::render('RealEstate/Show', [
            'listing' => $listing,
        ]);
    }

    public function apiIndex()
    {
        return Listing::query()->where('is_published', true)->latest()->paginate(20);
    }

    public function apiShow(string $slug)
    {
        return Listing::query()->where('slug', $slug)->firstOrFail();
    }
}
