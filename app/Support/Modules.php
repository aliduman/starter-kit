<?php

namespace App\Support;

/**
 * Read-only view over config/modules.php so every consumer (loader, Filament
 * panel, shared Inertia props, routes) resolves module state the same way.
 */
class Modules
{
    /**
     * Every registered module, enabled or not.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return config('modules.list', []);
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(string $key): array
    {
        return config("modules.list.{$key}", []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function enabled(): array
    {
        return collect(static::all())
            ->filter(fn (array $module) => (bool) ($module['enabled'] ?? false))
            ->all();
    }

    public static function isEnabled(string $key): bool
    {
        return (bool) config("modules.list.{$key}.enabled", false);
    }

    /** Modülün panel izni (config `permission`, varsayılan manage-{anahtar}). */
    public static function permission(string $key): string
    {
        return config("modules.list.{$key}.permission") ?? 'manage-'.$key;
    }

    /** Sınıfın ait olduğu modülün anahtarı (App\Modules\{Namespace}\...); modül dışı sınıf için null. */
    public static function keyOf(string $class): ?string
    {
        if (! str_starts_with($class, 'App\\Modules\\')) {
            return null;
        }

        $namespace = explode('\\', $class)[2] ?? null;
        $key = collect(static::all())->search(fn (array $module) => $module['namespace'] === $namespace);

        return $key === false ? null : $key;
    }

    public static function path(array $module, string $relative = ''): string
    {
        return app_path('Modules/'.$module['namespace'].($relative !== '' ? '/'.$relative : ''));
    }

    public static function namespace(array $module, string $relative = ''): string
    {
        return 'App\\Modules\\'.$module['namespace'].($relative !== '' ? '\\'.$relative : '');
    }
}
