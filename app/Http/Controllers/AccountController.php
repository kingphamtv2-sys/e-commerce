<?php

namespace App\Http\Controllers;

use App\Services\StorefrontPageDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, StorefrontPageDataService $pages): View
    {
        $user = $request->user();
        $orders = $user->orders();

        return view('account.index', $pages->data($request, [
            'recentOrders' => (clone $orders)->latest('placed_at')->limit(5)->get(),
            'pendingOrders' => (clone $orders)->whereIn('order_status', ['pending', 'confirmed', 'processing'])->count(),
            'unpaidOrders' => (clone $orders)->whereIn('payment_status', ['unpaid', 'pending', 'failed'])->count(),
            'defaultAddress' => $user->customerAddresses()->where('is_default_shipping', true)->first(),
        ]));
    }
}
