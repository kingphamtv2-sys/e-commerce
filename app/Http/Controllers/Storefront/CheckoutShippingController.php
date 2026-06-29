<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectShippingMethodRequest;
use App\Models\ShippingMethod;
use App\Services\CheckoutService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutShippingController extends Controller
{
    public function methods(Request $request, CheckoutService $checkoutService): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                ...$checkoutService->shippingMethodsPayload($request),
            ]);
        } catch (DomainException $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function select(SelectShippingMethodRequest $request, CheckoutService $checkoutService): JsonResponse
    {
        try {
            $method = ShippingMethod::query()->with('zone')->findOrFail($request->integer('shipping_method_id'));
            $checkoutService->selectShippingMethod($request, $method);

            return response()->json([
                'success' => true,
                'message' => __('storefront.shipping_method_selected'),
                ...$checkoutService->shippingMethodsPayload($request),
                'summary' => $checkoutService->summaryPayload($request),
            ]);
        } catch (DomainException $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function recalculate(Request $request, CheckoutService $checkoutService): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                ...$checkoutService->shippingMethodsPayload($request),
                'summary' => $checkoutService->summaryPayload($request),
            ]);
        } catch (DomainException $exception) {
            return $this->error($exception->getMessage());
        }
    }

    private function error(string $message): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => ['shipping' => [$message]]], 422);
    }
}
