<?php

namespace App\Modules\Media\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Media\Models\Article;
use App\Support\Seo\Seo;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        $articles = Article::query()
            ->published()
            ->latest('published_at')
            ->paginate(15);

        return Inertia::render('Media/Index', [
            'articles' => $articles,
            // Boş liste ince içerik: dizine girmesin (sitemap'te de yok).
            'head' => Seo::page(['title' => 'Haberler', 'noindex' => $articles->isEmpty()]),
        ]);
    }

    public function show(string $slug): Response
    {
        $article = Article::query()->published()->with('author')->where('slug', $slug)->firstOrFail();

        return Inertia::render('Media/Show', [
            'article' => $article,
            'head' => Seo::page([
                'title' => $article->title,
                'description' => $article->excerpt ?: $article->body,
                'type' => 'article',
                'published_time' => $article->published_at,
                'modified_time' => $article->updated_at,
                'author' => $article->author?->name,
                'section' => $article->category,
                'schema' => [array_filter([
                    '@type' => 'Article',
                    'headline' => $article->title,
                    'datePublished' => $article->published_at?->toAtomString(),
                    'dateModified' => $article->updated_at?->toAtomString(),
                    'author' => $article->author ? ['@type' => 'Person', 'name' => $article->author->name] : null,
                    'publisher' => Seo::organizationRef(),
                    'mainEntityOfPage' => url()->current(),
                ])],
            ]),
        ]);
    }

    public function apiIndex()
    {
        return Article::query()
            ->published()
            ->latest('published_at')
            ->paginate(15);
    }

    public function apiShow(string $slug)
    {
        return Article::query()->published()->where('slug', $slug)->firstOrFail();
    }
}
