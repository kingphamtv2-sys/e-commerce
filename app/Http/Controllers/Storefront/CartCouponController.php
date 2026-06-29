<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Services\CartService;
use App\Services\CouponService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartCouponController extends Controller
{
    public function store(ApplyCouponRequest $request, CartService $cartService, CouponService $couponService): JsonResponse
    {
        $cart = $cartService->currentCart($request);
        [$currency, $baseCurrency] = $cartService->currencies($request);

        if (! $cart || $cart->cartItems->isEmpty()) {
            return $this->error(__('storefront.coupon_cart_empty'));
        }

        try {
            $couponService->applyToCart($cart, $request->validated('code'), $request->user(), $cartService->items($cart, $currency, $baseCurrency));
        } catch (DomainException $exception) {
            return $this->error($exception->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => __('storefront.coupon_applied'),
            ...$cartService->responsePayload($request),
        ]);
    }

    public function destroy(Request $request, CartService $cartService, CouponService $couponService): JsonResponse
    {
        $couponService->removeFromCart($cartService->currentCart($request));

        return response()->json([
            'success' => true,
            'message' => __('storefront.coupon_removed'),
            ...$cartService->responsePayload($request),
        ]);
    }

    private function error(string $message): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => ['code' => [$message]]], 422);
    }
}
