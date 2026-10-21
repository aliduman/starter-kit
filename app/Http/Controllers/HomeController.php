<?php

namespace App\Http\Controllers;

use App\Models\HomepageSection;
use App\Support\Homepage\HomepageBlocks;
use App\Support\Modules;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request, HomepageBlocks $blocks): Response
    {
        return Inertia::render('Home', [
            // Paneldeki sırayla açık bölümler; modülü kapalı (kayıtlı olmayan) bileşen atlanır.
            'sections' => HomepageSection::query()
                ->active()
                ->get()
                ->filter(fn (HomepageSection $section) => $blocks->has($section->key))
                ->map(fn (HomepageSection $section) => $blocks->render($section))
                ->values(),
            'modules' => collect(Modules::all())
                ->map(fn ($module, $key) => [
                    'key' => $key,
                    'name' => $module['name'],
                    'description' => $module['description'] ?? null,
                    'enabled' => Modules::isEnabled($key),
                ])
                ->values(),
        ]);
    }
}
