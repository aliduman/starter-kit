<?php

namespace App\Modules\Media\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Media\Models\Article;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Media/Index', [
            'articles' => Article::query()
                ->where('is_published', true)
                ->latest('published_at')
                ->paginate(15),
        ]);
    }

    public function show(string $slug): Response
    {
        $article = Article::query()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Media/Show', [
            'article' => $article,
        ]);
    }

    public function apiIndex()
    {
        return Article::query()
            ->where('is_published', true)
            ->latest('published_at')
            ->paginate(15);
    }

    public function apiShow(string $slug)
    {
        return Article::query()->where('slug', $slug)->firstOrFail();
    }
}
