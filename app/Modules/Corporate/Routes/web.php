<?php

use App\Modules\Corporate\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('pages/{slug}', [PageController::class, 'show'])->name('corporate.pages.show');
Route::get('about', [PageController::class, 'about'])->name('corporate.about');
Route::get('contact', [PageController::class, 'contact'])->name('corporate.contact');
