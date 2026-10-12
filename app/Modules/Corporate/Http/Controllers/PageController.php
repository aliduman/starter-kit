<?php

namespace App\Modules\Corporate\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Corporate\Models\Page;
use App\Support\Seo\Seo;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::query()->published()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Corporate/PageShow', [
            'page' => $page,
            'head' => Seo::page([
                'title' => $page->title,
                'description' => $page->excerpt ?: $page->body,
                'modified_time' => $page->updated_at,
            ]),
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('Corporate/About', [
            'head' => Seo::page(['title' => 'Hakkımızda']),
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('Corporate/Contact', [
            'head' => Seo::page(['title' => 'İletişim']),
        ]);
    }

    public function index()
    {
        return Page::query()->published()->orderBy('title')->paginate(20);
    }

    public function showApi(string $slug)
    {
        return Page::query()->published()->where('slug', $slug)->firstOrFail();
    }
}
