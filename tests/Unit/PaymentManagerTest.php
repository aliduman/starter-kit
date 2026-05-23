<?php

use App\Modules\Ecommerce\Services\Payment\PaymentGateway;
use App\Modules\Ecommerce\Services\Payment\PaymentManager;

it('returns the registered gateway driver', function () {
    $manager = new PaymentManager;

    $manager->register('fake', fn () => new class implements PaymentGateway
    {
        public function charge(array $payload): array
        {
            return ['reference' => 'fake-1', 'status' => 'ok'];
        }

        public function refund(string $reference, array $payload = []): array
        {
            return ['reference' => $reference, 'status' => 'refunded'];
        }
    });

    $driver = $manager->driver('fake');

    expect($driver->charge([])['reference'])->toBe('fake-1');
});

it('throws on unknown gateway', function () {
    $manager = new PaymentManager;

    expect(fn () => $manager->driver('missing'))
        ->toThrow(InvalidArgumentException::class);
});
