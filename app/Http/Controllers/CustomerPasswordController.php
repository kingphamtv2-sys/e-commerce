<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\UpdateCustomerPasswordRequest;
use App\Services\StorefrontPageDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerPasswordController extends Controller
{
    public function edit(Request $request, StorefrontPageDataService $pages): View
    {
        return view('account.password.edit', $pages->data($request));
    }

    public function update(UpdateCustomerPasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return back()->with('success', __('account.password_updated'));
    }
}
