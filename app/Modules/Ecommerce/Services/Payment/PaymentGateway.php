<?php

namespace App\Modules\Ecommerce\Services\Payment;

interface PaymentGateway
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{reference: string, status: string, raw?: mixed}
     */
    public function charge(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function refund(string $reference, array $payload = []): array;
}
