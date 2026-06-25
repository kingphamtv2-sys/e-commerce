<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOnlinePaymentSettingsRequest;
use App\Services\OnlinePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OnlinePaymentSettingsController extends Controller
{
    public function edit(OnlinePaymentService $service): View
    {
        return view('admin.settings.online-payment', ['method' => $service->method()]);
    }

    public function update(UpdateOnlinePaymentSettingsRequest $request, OnlinePaymentService $service): RedirectResponse
    {
        $method = $service->method();
        $credentials = $method->credentials ?? [];
        if ($request->filled('secret_key')) {
            $credentials['secret_key'] = $request->validated('secret_key');
        }
        if ($request->boolean('enabled') && blank($credentials['secret_key'] ?? null)) {
            return back()->withInput()->withErrors(['secret_key' => __('admin.online_payment.secret_required')]);
        }

        $method->update([
            'name' => $request->validated('name') ?: 'Online Payment',
            'description' => $request->validated('description'),
            'instruction' => $request->validated('instruction'),
            'gateway_code' => $request->validated('gateway_code') ?: 'mock',
            'environment' => $request->validated('environment'),
            'credentials' => $credentials,
            'min_order_amount' => $request->validated('min_order_amount'),
            'max_order_amount' => $request->validated('max_order_amount'),
            'sort_order' => $request->validated('sort_order'),
            'status' => $request->boolean('enabled') ? 'active' : 'inactive',
        ]);

        return back()->with('success', __('admin.online_payment.updated'));
    }
}
