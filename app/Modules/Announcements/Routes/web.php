<?php

use App\Modules\Announcements\Http\Controllers\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::middleware('cacheResponse')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::get('{slug}', [AnnouncementController::class, 'show'])->name('show');
});
