<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OnlinePaymentService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnlinePaymentController extends Controller
{
    public function select(Request $request, string $token, OnlinePaymentService $service): JsonResponse|RedirectResponse
    {
        try {
            $session = $service->select($request, $token);
        } catch (DomainException $exception) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $exception->getMessage()], 422)
                : back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => __('storefront.payment_online_selected'),
            'payment_method_code' => $session->payment_method_code,
            'ready_to_order' => true,
            'order_url' => route('checkout.order.pay', $session->token),
            'order_label' => __('storefront.place_order_and_pay'),
            'order_loading_label' => __('storefront.redirecting_to_payment'),
        ]);
    }

    public function placeAndPay(Request $request, string $token, OnlinePaymentService $service): JsonResponse|RedirectResponse
    {
        try {
            $result = $service->createOrderAndPayment($request, $token);
        } catch (DomainException $exception) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $exception->getMessage()], 422)
                : back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return $request->expectsJson()
            ? response()->json(['success' => true, 'redirect_url' => $result['redirect_url']])
            : redirect()->away($result['redirect_url']);
    }

    public function retry(Request $request, Order $order, OnlinePaymentService $service): RedirectResponse
    {
        try {
            $result = $service->retry($request, $order);
        } catch (DomainException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return redirect()->away($result['redirect_url']);
    }
}
