<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Home', [
            'modules' => collect(config('modules.list'))
                ->map(fn ($cfg, $key) => [
                    'key' => $key,
                    'name' => $cfg['name'],
                    'description' => $cfg['description'] ?? null,
                    'enabled' => (bool) ($cfg['enabled'] ?? false),
                ])
                ->values(),
        ]);
    }
}
