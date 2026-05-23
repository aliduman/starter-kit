<?php

use App\Modules\RealEstate\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;

Route::get('listings', [ListingController::class, 'apiIndex']);
Route::get('listings/{slug}', [ListingController::class, 'apiShow']);
