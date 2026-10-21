<?php

use App\Modules\Corporate\Http\Controllers\ContactController;
use App\Modules\Corporate\Http\Controllers\PageController;
use App\Support\Modules;
use Illuminate\Support\Facades\Route;

// Adresler sitenin diline göre config/modules.php `corporate.paths`'ten gelir (ör. 'iletisim').
$paths = Modules::get('corporate')['paths'] ?? [];

Route::middleware('cacheResponse')->group(function () use ($paths) {
    Route::get(($paths['pages'] ?? 'pages').'/{slug}', [PageController::class, 'show'])->name('pages.show');
    Route::get($paths['about'] ?? 'about', [PageController::class, 'about'])->name('about');
    Route::get($paths['contact'] ?? 'contact', [ContactController::class, 'show'])->name('contact');
});

Route::post($paths['contact'] ?? 'contact', [ContactController::class, 'store'])
    ->middleware(['honeypot', 'throttle:5,1'])
    ->name('contact.store');
