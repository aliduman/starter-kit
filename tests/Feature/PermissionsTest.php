<?php

use App\Filament\Resources\MenuItemResource;
use App\Models\User;
use App\Modules\Announcements\Filament\Resources\AnnouncementResource;
use App\Modules\Ecommerce\Filament\Resources\ProductResource;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

it('builds roles from config, including module permissions', function () {
    config(['permissions.roles.broadcaster' => ['manage-announcements', 'manage-unknown']]);

    seed(RolesAndPermissionsSeeder::class);

    expect(Role::findByName('broadcaster')->permissions->pluck('name')->all())->toBe(['manage-announcements'])
        ->and(Role::findByName('super-admin')->permissions->pluck('name'))->toContain('manage-announcements', 'manage-site');
});

it('opens a module resource only to roles with its permission', function () {
    $editor = panelUser('editor');

    actingAs($editor)->get(AnnouncementResource::getUrl('index'))->assertOk();
    actingAs($editor)->get(ProductResource::getUrl('index'))->assertForbidden();
    actingAs($editor)->get(MenuItemResource::getUrl('index'))->assertForbidden();
});

it('lets only configured roles into the panel', function () {
    seed(RolesAndPermissionsSeeder::class);
    Role::findOrCreate('member');

    $member = User::create(['name' => 'Üye', 'email' => 'member@example.com', 'password' => 'password']);
    $member->assignRole('member');

    actingAs($member)->get('/'.config('filament.path'))->assertForbidden();
});
