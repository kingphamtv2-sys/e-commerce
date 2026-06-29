<?php

namespace App\Payments;

use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\Gateways\MockPaymentGateway;
use DomainException;

class PaymentGatewayManager
{
    public function __construct(private readonly MockPaymentGateway $mock) {}

    public function gateway(string $code): PaymentGatewayInterface
    {
        return match ($code) {
            'mock' => $this->mock,
            default => throw new DomainException(__('storefront.payment_gateway_unsupported')),
        };
    }
}
