<?php

use App\Http\Controllers\ContentPathController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SetupController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Support\Routing\ContentPaths;
use App\Support\Routing\TrailingSlash;
use App\Support\Seo\Sitemap;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Modül route'ları ModuleServiceProvider tarafından otomatik yüklenir.
*/

Route::get('/', [HomeController::class, 'index'])->middleware('cacheResponse')->name('home');

// Arama motorları: adresleri aktif modüller App\Support\Seo\Sitemap'e kaydeder.
Route::get('/sitemap.xml', fn (Sitemap $sitemap) => response()
    ->view('seo.sitemap', ['entries' => $sitemap->entries()])
    ->header('Content-Type', 'application/xml; charset=UTF-8'))
    ->middleware('cacheResponse')
    ->name('sitemap');

Route::get('/robots.txt', fn () => response(
    config('seo.indexable')
        ? implode("\n", ['User-agent: *', 'Disallow: /'.config('filament.path'), 'Disallow: /livewire', 'Disallow: /api', '', 'Sitemap: '.route('sitemap')])."\n"
        : "User-agent: *\nDisallow: /\n",
)->header('Content-Type', 'text/plain; charset=UTF-8'))->name('robots');

// Site araması: kaynakları modüller App\Support\Search\SearchSources'a kaydeder; sonuç sayfaları dizine girmez.
Route::get('/arama', SearchController::class)->middleware(TrailingSlash::MIDDLEWARE)->name('search');

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Terminal'siz hosting için kurulum (SETUP_TOKEN korumalı; bkz. CPANEL_DEPLOY.md)
|--------------------------------------------------------------------------
| Session'sız çalışır: ilk kurulumda veritabanı boşken de açılır (SESSION_DRIVER=database olsa bile).
*/
Route::get('/kurulum/{token}', SetupController::class)
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class, HandleInertiaRequests::class])
    ->middleware('throttle:5,1')
    ->name('setup');

/*
|--------------------------------------------------------------------------
| Kökteki içerik adresleri (/{yol}): App\Support\Routing\ContentPaths
|--------------------------------------------------------------------------
| Diğer tüm rotalardan sonra kayıtlı olmalı: statik adresler önce eşleşir. Sistem alanları ve nokta içeren
| (dosya) adresler içerik değildir.
*/
Route::get('/{path}', ContentPathController::class)
    ->where('path', '(?!(?:'.ContentPaths::systemPrefixes().')(?:/|$))[^/.]+(?:/[^/.]+)*')
    ->middleware([TrailingSlash::MIDDLEWARE, 'cacheResponse'])
    ->name('content');
