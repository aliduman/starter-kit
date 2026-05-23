<?php

namespace App\Modules\Corporate\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Corporate\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::query()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Corporate/PageShow', [
            'page' => $page,
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('Corporate/About');
    }

    public function contact(): Response
    {
        return Inertia::render('Corporate/Contact');
    }

    public function index()
    {
        return Page::query()->orderBy('title')->paginate(20);
    }

    public function showApi(string $slug)
    {
        return Page::query()->where('slug', $slug)->firstOrFail();
    }
}
