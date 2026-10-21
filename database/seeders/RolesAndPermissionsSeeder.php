<?php

namespace Database\Seeders;

use App\Support\Modules;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/** İzinler ve roller config/permissions.php ile modüllerin `permission` anahtarından kurulur (tekrar çalıştırılabilir). */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('cache:forget', ['key' => 'spatie.permission.cache']);

        $permissions = collect(config('permissions.permissions', []))
            ->merge(array_map(Modules::permission(...), array_keys(Modules::all())))
            ->unique()
            ->values();

        foreach ($permissions as $name) {
            Permission::findOrCreate($name);
        }

        foreach (config('permissions.roles', []) as $name => $granted) {
            Role::findOrCreate($name)->syncPermissions(
                $granted === '*' ? $permissions->all() : $permissions->intersect($granted)->values()->all(),
            );
        }
    }
}
