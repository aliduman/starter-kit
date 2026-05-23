<?php

use App\Modules\Ecommerce\Http\Controllers\CheckoutController;
use App\Modules\Ecommerce\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('ecommerce.products.index');
Route::get('products/{slug}', [ProductController::class, 'show'])->name('ecommerce.products.show');

Route::middleware('auth')->group(function () {
    Route::get('checkout', [CheckoutController::class, 'show'])->name('ecommerce.checkout');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('ecommerce.checkout.store');
});
