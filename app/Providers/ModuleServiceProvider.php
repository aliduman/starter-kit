<?php

namespace App\Providers;

use App\Support\Modules;
use App\Support\Routing\TrailingSlash;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Boots every module enabled in config/modules.php:
 *  - registers its own service provider (bindings, observers, policies)
 *  - Routes/web.php (web + TrailingSlash middleware, `prefix`, route names `{key}.`)
 *  - Routes/api.php (api middleware, /api/{prefix|key}, route names `api.{key}.`)
 *  - Views/ (Blade namespace = module key, e.g. view('corporate::page'))
 *  - Database/migrations/
 *  - Console/*.php artisan commands
 * Filament resources are discovered by AdminPanelProvider using the same list.
 */
class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach (Modules::enabled() as $module) {
            $provider = $module['provider'] ?? null;

            if ($provider && class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    public function boot(): void
    {
        foreach (Modules::enabled() as $key => $module) {
            $prefix = $module['prefix'] ?? '';

            if (file_exists($webRoutes = Modules::path($module, 'Routes/web.php'))) {
                Route::middleware(['web', TrailingSlash::MIDDLEWARE])
                    ->prefix($prefix)
                    ->name($key.'.')
                    ->group($webRoutes);
            }

            if (file_exists($apiRoutes = Modules::path($module, 'Routes/api.php'))) {
                Route::middleware('api')
                    ->prefix('api/'.($prefix ?: $key))
                    ->name('api.'.$key.'.')
                    ->group($apiRoutes);
            }

            if (is_dir($views = Modules::path($module, 'Views'))) {
                $this->loadViewsFrom($views, $key);
            }

            if (is_dir($migrations = Modules::path($module, 'Database/migrations'))) {
                $this->loadMigrationsFrom($migrations);
            }

            if ($this->app->runningInConsole() && is_dir($console = Modules::path($module, 'Console'))) {
                foreach (glob($console.'/*.php') ?: [] as $file) {
                    $class = Modules::namespace($module, 'Console\\'.basename($file, '.php'));

                    if (class_exists($class) && is_subclass_of($class, Command::class)) {
                        $this->commands($class);
                    }
                }
            }
        }
    }
}
