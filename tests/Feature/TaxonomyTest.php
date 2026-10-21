<?php

use App\Models\Category;
use App\Models\Tag;
use App\Modules\Media\Models\Article;

it('keeps category slugs unique within a content type only', function () {
    $a = Category::create(['type' => 'media', 'name' => 'Gündem']);
    $b = Category::create(['type' => 'media', 'name' => 'Gündem']);
    $c = Category::create(['type' => 'radio', 'name' => 'Gündem']);

    expect([$a->slug, $b->slug, $c->slug])->toBe(['gundem', 'gundem-1', 'gundem']);
});

it('links articles to shared categories and tags', function () {
    $category = Category::create(['type' => Article::CATEGORY_TYPE, 'name' => 'Kültür']);
    $article = Article::create(['title' => 'Etiketli', 'category_id' => $category->id, 'is_published' => true]);
    $article->tags()->attach([Tag::create(['name' => 'Radyo'])->id, Tag::create(['name' => 'Arşiv'])->id]);

    expect($article->fresh()->category->name)->toBe('Kültür')
        ->and($article->tags->pluck('name')->all())->toBe(['Arşiv', 'Radyo']);
});
