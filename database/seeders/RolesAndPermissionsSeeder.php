<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('cache:forget', ['key' => 'spatie.permission.cache']);

        $permissions = [
            'manage-corporate',
            'manage-ecommerce',
            'manage-realestate',
            'manage-media',
            'manage-users',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name);
        }

        $superAdmin = Role::findOrCreate('super-admin');
        $admin = Role::findOrCreate('admin');
        $editor = Role::findOrCreate('editor');

        $superAdmin->syncPermissions(Permission::all());
        $admin->syncPermissions([
            'manage-corporate',
            'manage-ecommerce',
            'manage-realestate',
            'manage-media',
        ]);
        $editor->syncPermissions([
            'manage-corporate',
            'manage-media',
        ]);
    }
}
