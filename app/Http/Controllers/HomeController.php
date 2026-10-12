<?php

namespace App\Http\Controllers;

use App\Support\Modules;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Home', [
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
