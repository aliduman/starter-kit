<?php

use App\Modules\Corporate\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('pages', [PageController::class, 'index']);
Route::get('pages/{slug}', [PageController::class, 'showApi']);
