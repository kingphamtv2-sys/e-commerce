<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\OnlinePaymentService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, string $gateway, OnlinePaymentService $service): JsonResponse
    {
        try {
            $log = $service->processWebhook($gateway, $request->all(), $request->headers->all());
        } catch (DomainException $exception) {
            return response()->json(['received' => false, 'message' => $exception->getMessage()], 422);
        }

        return response()->json(['received' => true, 'processed' => $log->processed]);
    }
}
