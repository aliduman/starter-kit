<?php

use App\Modules\Media\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;

Route::get('articles', [ArticleController::class, 'apiIndex']);
Route::get('articles/{slug}', [ArticleController::class, 'apiShow']);
