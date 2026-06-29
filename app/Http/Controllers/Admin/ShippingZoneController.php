<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShippingZoneRequest;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingZoneController extends Controller
{
    public function index(Request $request): View
    {
        $zones = ShippingZone::query()
            ->withCount('methods')
            ->when($request->filled('keyword'), function ($query) use ($request): void {
                $keyword = trim((string) $request->input('keyword'));
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.shipping.zones.index', [
            'zones' => $zones,
            'filters' => $request->only(['keyword', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.shipping.zones.create', [
            'zone' => new ShippingZone(['status' => ShippingZone::STATUS_ACTIVE]),
        ]);
    }

    public function store(ShippingZoneRequest $request): RedirectResponse
    {
        $zone = ShippingZone::query()->create($request->validated());

        return redirect()->route('admin.shipping.zones.edit', $zone)->with('success', __('admin.shipping.zones.created'));
    }

    public function edit(ShippingZone $zone): View
    {
        return view('admin.shipping.zones.edit', ['zone' => $zone]);
    }

    public function update(ShippingZoneRequest $request, ShippingZone $zone): RedirectResponse
    {
        $zone->update($request->validated());

        return back()->with('success', __('admin.shipping.zones.updated'));
    }

    public function destroy(ShippingZone $zone): RedirectResponse
    {
        if ($zone->methods()->exists() || $zone->orders()->exists()) {
            $zone->update(['status' => ShippingZone::STATUS_INACTIVE]);

            return redirect()->route('admin.shipping.zones.index')->with('success', __('admin.shipping.zones.disabled_has_methods'));
        }

        $zone->delete();

        return redirect()->route('admin.shipping.zones.index')->with('success', __('admin.shipping.zones.deleted'));
    }
}
