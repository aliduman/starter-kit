<?php

use App\Models\Tag;
use App\Models\User;
use App\Modules\Media\Filament\Resources\ArticleResource\Pages\CreateArticle;
use App\Modules\Media\Filament\Resources\ArticleResource\Pages\EditArticle;
use App\Modules\Media\Models\Article;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(RolesAndPermissionsSeeder::class);

    $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
    $admin->assignRole('super-admin');
    actingAs($admin);
});

it('links typed tags on save and creates the missing ones', function () {
    $existing = Tag::create(['name' => 'aile']);

    Livewire::test(CreateArticle::class)
        ->fillForm(['title' => 'Ailede Çocuk Eğitimi', 'tags' => ['aile', 'çocuk', ' çocuk ']])
        ->call('create')
        ->assertHasNoFormErrors();

    $article = Article::where('title', 'Ailede Çocuk Eğitimi')->firstOrFail();

    expect($article->tags->pluck('name')->sort()->values()->all())->toBe(['aile', 'çocuk'])
        ->and($article->tags->firstWhere('name', 'aile')->id)->toBe($existing->id)
        ->and(Tag::count())->toBe(2);
});

it('loads current tags, suggests existing names and unlinks removed tags', function () {
    Tag::create(['name' => 'kadın']);
    $article = Article::create(['title' => 'Ailede Çocuk Eğitimi']);
    $article->tags()->sync([Tag::create(['name' => 'aile'])->id, Tag::create(['name' => 'çocuk'])->id]);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->assertFormSet(fn (array $state) => expect(collect($state['tags'])->sort()->values()->all())->toBe(['aile', 'çocuk']))
        ->assertFormFieldExists('tags', fn ($field) => in_array('kadın', $field->getSuggestions(), true))
        ->fillForm(['tags' => ['çocuk', 'kadın']])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($article->fresh()->tags->pluck('name')->sort()->values()->all())->toBe(['kadın', 'çocuk']);
});
