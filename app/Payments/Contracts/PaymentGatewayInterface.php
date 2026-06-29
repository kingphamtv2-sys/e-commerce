<?php

namespace App\Payments\Contracts;

use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;

interface PaymentGatewayInterface
{
    public function code(): string;

    /** @return array{redirect_url:string, request_payload:array} */
    public function createPayment(PaymentTransaction $transaction, PaymentMethod $method): array;

    /** @return array{valid:bool,status:string,transaction_number:?string,gateway_transaction_id:?string,amount:?float,currency_code:?string,event_id:?string,payload:array,error:?string} */
    public function verifyReturn(array $payload, PaymentMethod $method): array;

    /** @return array{valid:bool,status:string,transaction_number:?string,gateway_transaction_id:?string,amount:?float,currency_code:?string,event_id:?string,payload:array,error:?string} */
    public function verifyWebhook(array $payload, PaymentMethod $method): array;
}
