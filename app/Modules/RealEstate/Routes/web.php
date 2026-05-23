<?php

use App\Modules\RealEstate\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ListingController::class, 'index'])->name('realestate.listings.index');
Route::get('{slug}', [ListingController::class, 'show'])->name('realestate.listings.show');
