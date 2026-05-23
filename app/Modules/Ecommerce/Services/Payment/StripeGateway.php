<?php

namespace App\Modules\Ecommerce\Services\Payment;

use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\StripeClient;

class StripeGateway implements PaymentGateway
{
    protected StripeClient $client;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config = [])
    {
        $this->client = new StripeClient($this->config['secret'] ?? '');
    }

    public function charge(array $payload): array
    {
        $intent = $this->client->paymentIntents->create([
            'amount' => (int) round(($payload['amount'] ?? 0) * 100),
            'currency' => strtolower($payload['currency'] ?? 'usd'),
            'metadata' => [
                'user_id' => optional($payload['user'] ?? null)->id,
            ],
        ]);

        return [
            'reference' => $intent->id,
            'status' => $intent->status,
            'client_secret' => $intent->client_secret,
            'raw' => $intent->toArray(),
        ];
    }

    public function refund(string $reference, array $payload = []): array
    {
        $refund = $this->client->refunds->create([
            'payment_intent' => $reference,
            'amount' => isset($payload['amount'])
                ? (int) round($payload['amount'] * 100)
                : null,
        ]);

        return [
            'reference' => $refund->id,
            'status' => $refund->status,
            'raw' => $refund->toArray(),
        ];
    }
}
