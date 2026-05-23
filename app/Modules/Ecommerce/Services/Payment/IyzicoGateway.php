<?php

namespace App\Modules\Ecommerce\Services\Payment;

use Iyzipay\Model\Payment;
use Iyzipay\Options;
use Iyzipay\Request\CreatePaymentRequest;

class IyzicoGateway implements PaymentGateway
{
    protected Options $options;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config = [])
    {
        $this->options = new Options;
        $this->options->setApiKey($this->config['api_key'] ?? '');
        $this->options->setSecretKey($this->config['secret_key'] ?? '');
        $this->options->setBaseUrl($this->config['base_url'] ?? 'https://sandbox-api.iyzipay.com');
    }

    public function charge(array $payload): array
    {
        $request = new CreatePaymentRequest;
        $request->setLocale('tr');
        $request->setConversationId((string) ($payload['conversation_id'] ?? uniqid('conv_', true)));
        $request->setPrice((string) ($payload['amount'] ?? 0));
        $request->setPaidPrice((string) ($payload['amount'] ?? 0));
        $request->setCurrency(strtoupper($payload['currency'] ?? 'TRY'));
        $request->setInstallment(1);
        $request->setBasketId('B'.uniqid());
        $request->setPaymentChannel('WEB');
        $request->setPaymentGroup('PRODUCT');

        $response = Payment::create($request, $this->options);

        return [
            'reference' => $response->getPaymentId() ?? $request->getConversationId(),
            'status' => $response->getStatus() ?? 'pending',
            'raw' => $response->getRawResult(),
        ];
    }

    public function refund(string $reference, array $payload = []): array
    {
        return [
            'reference' => $reference,
            'status' => 'manual',
            'raw' => null,
        ];
    }
}
