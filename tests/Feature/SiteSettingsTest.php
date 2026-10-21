<?php

use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

function panelUser(string $role): User
{
    seed(RolesAndPermissionsSeeder::class);

    $user = User::create(['name' => ucfirst($role), 'email' => "{$role}@example.com", 'password' => 'password']);
    $user->assignRole($role);

    return $user;
}

it('saves the settings form and shares only public values with the site', function () {
    actingAs(panelUser('admin'));

    Livewire::test(ManageSettings::class)
        ->set('data.site_name', 'Örnek Radyo')
        ->set('data.contact_email', 'iletisim@example.com')
        ->set('data.social_instagram', 'https://instagram.com/ornek')
        ->set('data.ga4_id', 'G-TEST123')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::get('site_name'))->toBe('Örnek Radyo')
        ->and(Setting::get('ga4_id'))->toBe('G-TEST123');

    get('/')
        ->assertInertia(fn ($page) => $page
            ->where('app.name', 'Örnek Radyo')
            ->where('settings.contact_email', 'iletisim@example.com')
            ->missing('settings.ga4_id'))
        ->assertSee('<title data-inertia="">Örnek Radyo</title>', false)
        ->assertSee('"sameAs":["https://instagram.com/ornek"]', false);
});

it('feeds analytics and verification from settings when the site is indexable', function () {
    config(['seo.indexable' => true]);
    Setting::store(['ga4_id' => 'G-FROMPANEL', 'google_site_verification' => 'panel-token']);

    get('/')
        ->assertInertia(fn ($page) => $page->where('analytics.ga4', 'G-FROMPANEL'))
        ->assertSee('name="google-site-verification" content="panel-token"', false);
});

it('keeps the settings page from users without the site permission', function () {
    actingAs(panelUser('editor'));

    get(ManageSettings::getUrl())->assertForbidden();
});
