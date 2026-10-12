<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\get;

it('does not exist while SETUP_TOKEN is empty', function () {
    config(['app.setup_token' => null]);

    get('/kurulum/anything')->assertNotFound();
});

it('rejects a wrong token', function () {
    config(['app.setup_token' => 'dogru-token']);

    get('/kurulum/yanlis-token')->assertNotFound();
});

it('migrates and clears caches with the right token', function () {
    config(['app.setup_token' => 'dogru-token']);
    Artisan::shouldReceive('call')->with('migrate', ['--force' => true])->once();
    Artisan::shouldReceive('call')->with('storage:link')->once();
    Artisan::shouldReceive('call')->with('optimize:clear')->once();
    Artisan::shouldReceive('output')->andReturn('');

    get('/kurulum/dogru-token')
        ->assertOk()
        ->assertSee('== migrate ==')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

it('creates no default admin when seeding production without SETUP_ADMIN_*', function () {
    app()->detectEnvironment(fn () => 'production');

    // db:seed asks for confirmation in production; run the seeder directly.
    $seed = fn () => app(DatabaseSeeder::class)->setContainer(app())->__invoke();

    $seed();
    expect(User::count())->toBe(0);

    config(['app.setup_admin' => ['email' => 'yonetici@example.com', 'password' => 'uzun-bir-sifre']]);
    $seed();
    expect(User::where('email', 'yonetici@example.com')->first()?->hasRole('super-admin'))->toBeTrue();
});

it('runs without a session so it works on an empty database', function () {
    $middleware = app('router')->gatherRouteMiddleware(app('router')->getRoutes()->getByName('setup'));

    expect($middleware)->not->toContain(StartSession::class)
        ->and($middleware)->toContain(EncryptCookies::class);
});
