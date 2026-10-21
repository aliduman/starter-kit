<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

it('renders the list and create pages of every resource and page', function () {
    seed(RolesAndPermissionsSeeder::class);

    $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
    $admin->assignRole('super-admin');

    $resources = Filament::getPanel('admin')->getResources();

    expect($resources)->not->toBeEmpty();

    foreach ($resources as $resource) {
        actingAs($admin)->get($resource::getUrl('index'))->assertOk();

        // Salt okunur kaynakların (işlem geçmişi, gelen mesajlar) ekleme sayfası yoktur.
        if ($resource::hasPage('create')) {
            actingAs($admin)->get($resource::getUrl('create'))->assertOk();
        }
    }

    foreach (Filament::getPanel('admin')->getPages() as $page) {
        actingAs($admin)->get($page::getUrl())->assertOk();
    }
});
