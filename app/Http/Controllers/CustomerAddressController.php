<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\CustomerAddressRequest;
use App\Models\CustomerAddress;
use App\Services\CustomerAddressService;
use App\Services\StorefrontPageDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerAddressController extends Controller
{
    public function index(Request $request, StorefrontPageDataService $pages): View
    {
        return view('account.addresses.index', $pages->data($request, [
            'addresses' => $request->user()->customerAddresses()
                ->orderByDesc('is_default_shipping')
                ->orderByDesc('is_default_billing')
                ->latest()
                ->get(),
        ]));
    }

    public function create(Request $request, StorefrontPageDataService $pages): View
    {
        return view('account.addresses.create', $pages->data($request, [
            'address' => new CustomerAddress,
        ]));
    }

    public function store(
        CustomerAddressRequest $request,
        CustomerAddressService $service,
    ): RedirectResponse {
        $service->create($request->user(), $request->validated());

        return redirect()->route('account.addresses.index')->with('success', __('account.address_created'));
    }

    public function edit(Request $request, int $address, StorefrontPageDataService $pages): View
    {
        return view('account.addresses.edit', $pages->data($request, [
            'address' => $this->ownedAddress($request, $address),
        ]));
    }

    public function update(
        CustomerAddressRequest $request,
        int $address,
        CustomerAddressService $service,
    ): RedirectResponse {
        $service->update($request->user(), $this->ownedAddress($request, $address), $request->validated());

        return redirect()->route('account.addresses.index')->with('success', __('account.address_updated'));
    }

    public function destroy(Request $request, int $address): RedirectResponse
    {
        $this->ownedAddress($request, $address)->delete();

        return redirect()->route('account.addresses.index')->with('success', __('account.address_deleted'));
    }

    public function setDefault(
        Request $request,
        int $address,
        CustomerAddressService $service,
    ): RedirectResponse {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['shipping', 'billing'])],
        ]);
        $service->setDefault(
            $request->user(),
            $this->ownedAddress($request, $address),
            $validated['type'],
        );

        return back()->with('success', __('account.default_address_updated'));
    }

    private function ownedAddress(Request $request, int $address): CustomerAddress
    {
        return $request->user()->customerAddresses()->findOrFail($address);
    }
}
