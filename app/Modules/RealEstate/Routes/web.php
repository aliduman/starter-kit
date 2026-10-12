<?php

use App\Modules\RealEstate\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;

Route::middleware('cacheResponse')->group(function () {
    Route::get('/', [ListingController::class, 'index'])->name('listings.index');
    Route::get('{slug}', [ListingController::class, 'show'])->name('listings.show');
});
