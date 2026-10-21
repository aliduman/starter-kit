<?php

use App\Modules\Corporate\Models\ContactMessage;

use function Pest\Laravel\from;
use function Pest\Laravel\get;

it('shows the contact page with the configured subjects', function () {
    get('/contact')->assertOk()->assertInertia(fn ($page) => $page
        ->component('Corporate/Contact')
        ->where('subjects.general', 'Genel'));
});

it('stores a message with the consent time', function () {
    from('/contact')->post('/contact', [
        'name' => 'Ayşe Yılmaz',
        'email' => 'ayse@example.com',
        'subject' => 'support',
        'message' => 'Yayın akışında bir hata gördüm.',
        'consent' => true,
    ])->assertRedirect('/contact')->assertSessionHas('success');

    $message = ContactMessage::sole();

    expect($message->status)->toBe('new')
        ->and($message->subject)->toBe('support')
        ->and($message->consented_at)->not->toBeNull();
});

it('requires consent and a known subject', function () {
    from('/contact')->post('/contact', [
        'name' => 'Ayşe',
        'email' => 'ayse@example.com',
        'subject' => 'unknown',
        'message' => 'Yeterince uzun bir mesaj.',
    ])->assertSessionHasErrors(['consent', 'subject']);

    expect(ContactMessage::count())->toBe(0);
});

it('silently drops submissions that fill the honeypot', function () {
    from('/contact')->post('/contact', [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'message' => 'Kazanmak için tıklayın!',
        'consent' => true,
        'website' => 'https://spam.example',
    ])->assertRedirect('/contact')->assertSessionHas('success');

    expect(ContactMessage::count())->toBe(0);
});

it('limits submissions per minute', function () {
    foreach (range(1, 5) as $i) {
        from('/contact')->post('/contact', ['name' => 'X'])->assertSessionHasErrors();
    }

    from('/contact')->post('/contact', ['name' => 'X'])->assertTooManyRequests();
});
