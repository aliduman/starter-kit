<?php

use App\Modules\Media\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ArticleController::class, 'index'])->name('media.articles.index');
Route::get('{slug}', [ArticleController::class, 'show'])->name('media.articles.show');
