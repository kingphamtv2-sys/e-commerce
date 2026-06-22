<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, CartService $cartService): RedirectResponse
    {
        $guestSessionId = $request->session()->get('cart_session_id', $request->session()->getId());

        $request->authenticate();

        $request->session()->regenerate();

        if ($request->user()->role === 'customer') {
            $cartService->mergeGuestCartIntoUser($guestSessionId, $request->user());
        }

        $route = in_array($request->user()->role, ['super_admin', 'admin', 'staff'], true)
            ? 'admin.dashboard'
            : 'account.index';

        return redirect()->route($route);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
