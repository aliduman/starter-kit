<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

it('renders the list and create pages of every module resource', function () {
    seed(RolesAndPermissionsSeeder::class);

    $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
    $admin->assignRole('super-admin');

    $resources = Filament::getPanel('admin')->getResources();

    expect($resources)->not->toBeEmpty();

    foreach ($resources as $resource) {
        actingAs($admin)->get($resource::getUrl('index'))->assertOk();
        actingAs($admin)->get($resource::getUrl('create'))->assertOk();
    }
});
