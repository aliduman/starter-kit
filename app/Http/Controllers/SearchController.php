<?php

namespace App\Http\Controllers;

use App\Support\Search\SearchSources;
use App\Support\Seo\Seo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    private const MIN_LENGTH = 2;

    public function __invoke(Request $request, SearchSources $sources): Response
    {
        $query = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        return Inertia::render('Search', [
            'query' => $query,
            'groups' => mb_strlen($query) >= self::MIN_LENGTH ? $sources->search($query) : [],
            'head' => Seo::page([
                'title' => $query === '' ? 'Arama' : "Arama: {$query}",
                'noindex' => true,
                'breadcrumbs' => [['Arama']],
            ]),
        ]);
    }
}
