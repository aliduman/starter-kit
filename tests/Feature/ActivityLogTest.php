<?php

use App\Models\Activity;
use App\Models\User;
use App\Modules\Corporate\Models\Page;

use function Pest\Laravel\actingAs;

it('records who created, changed and deleted a record', function () {
    $user = User::create(['name' => 'Editör', 'email' => 'editor@example.com', 'password' => 'password']);
    actingAs($user);

    $page = Page::create(['title' => 'Hakkımızda', 'body' => '<p>İlk</p>']);
    $page->update(['body' => '<p>İkinci</p>']);
    $page->touch(); // yalnızca zaman damgası: kayıt düşmez
    $page->delete();

    $log = Activity::query()->orderBy('id')->get();

    expect($log->pluck('event')->all())->toBe(['created', 'updated', 'deleted'])
        ->and($log->pluck('user_id')->unique()->all())->toBe([$user->id])
        ->and($log[0]->changes)->toHaveKey('title.new', 'Hakkımızda')
        ->and($log[0]->changes)->not->toHaveKey('created_at')
        ->and($log[1]->changes)->toBe(['body' => ['old' => 'İlk', 'new' => 'İkinci']])
        ->and($log[2]->subject_id)->toBe($page->id);
});

it('records visitor-made records without a user', function () {
    Page::create(['title' => 'Ziyaretçi']);

    expect(Activity::sole()->user_id)->toBeNull();
});
