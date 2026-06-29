<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShippingMethodRequest;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingMethodController extends Controller
{
    public function index(Request $request): View
    {
        $methods = ShippingMethod::query()
            ->with('zone')
            ->when($request->filled('keyword'), function ($query) use ($request): void {
                $keyword = trim((string) $request->input('keyword'));
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('zone'), fn ($query) => $query->where('shipping_zone_id', $request->input('zone')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.shipping.methods.index', [
            'methods' => $methods,
            'zones' => $this->zones(),
            'filters' => $request->only(['keyword', 'status', 'type', 'zone']),
        ]);
    }

    public function create(): View
    {
        return view('admin.shipping.methods.create', [
            'method' => new ShippingMethod([
                'type' => ShippingMethod::TYPE_FLAT_RATE,
                'status' => ShippingMethod::STATUS_ACTIVE,
                'base_fee' => 0,
            ]),
            'zones' => $this->zones(),
        ]);
    }

    public function store(ShippingMethodRequest $request): RedirectResponse
    {
        $method = ShippingMethod::query()->create($request->validated());

        return redirect()->route('admin.shipping.methods.edit', $method)->with('success', __('admin.shipping.methods.created'));
    }

    public function edit(ShippingMethod $method): View
    {
        return view('admin.shipping.methods.edit', [
            'method' => $method,
            'zones' => $this->zones(),
        ]);
    }

    public function update(ShippingMethodRequest $request, ShippingMethod $method): RedirectResponse
    {
        $method->update($request->validated());

        return back()->with('success', __('admin.shipping.methods.updated'));
    }

    public function destroy(ShippingMethod $method): RedirectResponse
    {
        if ($method->orders()->exists()) {
            $method->update(['status' => ShippingMethod::STATUS_INACTIVE]);

            return redirect()->route('admin.shipping.methods.index')->with('success', __('admin.shipping.methods.disabled_used'));
        }

        $method->delete();

        return redirect()->route('admin.shipping.methods.index')->with('success', __('admin.shipping.methods.deleted'));
    }

    private function zones()
    {
        return ShippingZone::query()->orderBy('sort_order')->orderBy('name')->get();
    }
}
