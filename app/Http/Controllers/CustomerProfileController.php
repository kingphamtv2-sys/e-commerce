<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\UpdateCustomerProfileRequest;
use App\Services\StorefrontPageDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerProfileController extends Controller
{
    public function edit(Request $request, StorefrontPageDataService $pages): View
    {
        return view('account.profile.edit', $pages->data($request, ['user' => $request->user()]));
    }

    public function update(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', __('account.profile_updated'));
    }
}
