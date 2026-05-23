<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Each module ships with its own provider that hooks into bindings,
     * Filament resources, events and policies. We boot whichever modules
     * are enabled in config/modules.php and register their web routes.
     */
    public function register(): void
    {
        foreach ($this->enabledModules() as $key => $config) {
            $provider = $config['provider'] ?? null;

            if ($provider && class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    public function boot(): void
    {
        foreach ($this->enabledModules() as $key => $config) {
            $base = base_path("app/Modules/{$config['namespace']}");

            $webRoutes = $base.'/Routes/web.php';
            $apiRoutes = $base.'/Routes/api.php';
            $views = $base.'/Views';
            $migrations = $base.'/Database/migrations';

            if (file_exists($webRoutes)) {
                Route::middleware('web')
                    ->prefix($config['prefix'] ?? null)
                    ->group($webRoutes);
            }

            if (file_exists($apiRoutes)) {
                Route::middleware('api')
                    ->prefix('api/'.($config['prefix'] ?? $key))
                    ->group($apiRoutes);
            }

            if (is_dir($views)) {
                $this->loadViewsFrom($views, $key);
            }

            if (is_dir($migrations)) {
                $this->loadMigrationsFrom($migrations);
            }
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function enabledModules(): array
    {
        return collect(config('modules.list', []))
            ->filter(fn ($cfg) => (bool) ($cfg['enabled'] ?? false))
            ->all();
    }
}
