<?php

namespace App\Filament\Concerns;

use App\Support\Modules;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use UnitEnum;

/**
 * Panel kaynağı yalnızca gerekli izni olan kullanıcıya açılır (super-admin her şeye erişir, AppServiceProvider).
 * İzin sınıftaki `$permission`'dır; yoksa kaynağın ait olduğu modülün izni (config/modules.php `permission`).
 * Tüm eylemler (liste, ekleme, düzenleme, silme) aynı izni ister; ince ayar gerekirse policy eklenir.
 */
trait ChecksPermission
{
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        if (! auth()->user()?->can(static::requiredPermission())) {
            return Response::deny();
        }

        return parent::getAuthorizationResponse($action, $record);
    }

    public static function requiredPermission(): string
    {
        if (property_exists(static::class, 'permission')) {
            return static::$permission;
        }

        $module = Modules::keyOf(static::class)
            ?? throw new LogicException(static::class.' bir modüle ait değil; $permission tanımlamalı.');

        return Modules::permission($module);
    }
}
