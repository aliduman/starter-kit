<?php

use App\Modules\Ecommerce\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('products', [ProductController::class, 'apiIndex']);
Route::get('products/{slug}', [ProductController::class, 'apiShow']);
