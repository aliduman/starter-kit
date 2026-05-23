<?php

namespace App\Modules\Ecommerce\Services\Payment;

use Closure;
use InvalidArgumentException;

class PaymentManager
{
    /** @var array<string, Closure(): PaymentGateway> */
    protected array $factories = [];

    /** @var array<string, PaymentGateway> */
    protected array $resolved = [];

    public function register(string $name, Closure $factory): void
    {
        $this->factories[$name] = $factory;
    }

    public function driver(string $name): PaymentGateway
    {
        if (! isset($this->factories[$name])) {
            throw new InvalidArgumentException("Payment gateway [{$name}] is not registered.");
        }

        return $this->resolved[$name] ??= ($this->factories[$name])();
    }
}
