<?php

use App\Modules\Corporate\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware('cacheResponse')->group(function () {
    Route::get('pages/{slug}', [PageController::class, 'show'])->name('pages.show');
    Route::get('about', [PageController::class, 'about'])->name('about');
    Route::get('contact', [PageController::class, 'contact'])->name('contact');
});
