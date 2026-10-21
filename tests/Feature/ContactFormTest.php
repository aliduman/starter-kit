<?php

use App\Modules\Corporate\Models\ContactMessage;

use function Pest\Laravel\from;
use function Pest\Laravel\get;

// Adres ve konular sitenin ayarıdır (config/modules.php corporate.paths / contact_subjects).
function contactPath(): string
{
    return route('corporate.contact', absolute: false);
}

function contactSubject(): string
{
    return array_key_first(ContactMessage::subjects());
}

it('shows the contact page with the configured subjects', function () {
    get(contactPath())->assertOk()->assertInertia(fn ($page) => $page
        ->component('Corporate/Contact')
        ->where('subjects', ContactMessage::subjects()));
});

it('stores a message with the consent time', function () {
    from(contactPath())->post(contactPath(), [
        'name' => 'Ayşe Yılmaz',
        'email' => 'ayse@example.com',
        'subject' => contactSubject(),
        'message' => 'Yayın akışında bir hata gördüm.',
        'consent' => true,
    ])->assertRedirect(contactPath())->assertSessionHas('success');

    $message = ContactMessage::sole();

    expect($message->status)->toBe('new')
        ->and($message->subject)->toBe(contactSubject())
        ->and($message->consented_at)->not->toBeNull();
});

it('requires consent and a known subject', function () {
    from(contactPath())->post(contactPath(), [
        'name' => 'Ayşe',
        'email' => 'ayse@example.com',
        'subject' => 'unknown',
        'message' => 'Yeterince uzun bir mesaj.',
    ])->assertSessionHasErrors(['consent', 'subject']);

    expect(ContactMessage::count())->toBe(0);
});

it('silently drops submissions that fill the honeypot', function () {
    from(contactPath())->post(contactPath(), [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'message' => 'Kazanmak için tıklayın!',
        'consent' => true,
        'website' => 'https://spam.example',
    ])->assertRedirect(contactPath())->assertSessionHas('success');

    expect(ContactMessage::count())->toBe(0);
});

it('limits submissions per minute', function () {
    foreach (range(1, 5) as $i) {
        from(contactPath())->post(contactPath(), ['name' => 'X'])->assertSessionHasErrors();
    }

    from(contactPath())->post(contactPath(), ['name' => 'X'])->assertTooManyRequests();
});
