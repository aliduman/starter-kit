<?php

use App\Modules\Media\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;

Route::middleware('cacheResponse')->group(function () {
    Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('{slug}', [ArticleController::class, 'show'])->name('articles.show');
});
