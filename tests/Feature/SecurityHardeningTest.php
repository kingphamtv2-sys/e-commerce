<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_view_another_customers_order_with_a_valid_token(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $order = $this->order($owner, str_repeat('a', 80));

        $this->actingAs($otherCustomer)
            ->get(route('orders.success', $order->success_token))
            ->assertForbidden();
    }

    public function test_owner_and_guest_can_only_open_their_expected_token_based_order_pages(): void
    {
        $this->seed([LanguageSeeder::class, CurrencySeeder::class]);
        $owner = User::factory()->create(['role' => 'customer']);
        $customerOrder = $this->order($owner, str_repeat('b', 80));
        $guestOrder = $this->order(null, str_repeat('c', 80));

        $this->actingAs($owner)
            ->get(route('orders.success', $customerOrder->success_token))
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->get(route('orders.success', $guestOrder->success_token))->assertOk();
        $this->get(route('orders.success', str_repeat('x', 80)))->assertNotFound();
    }

    public function test_sensitive_public_actions_have_rate_limiting_middleware(): void
    {
        foreach ([
            'cart.coupon.apply' => 'throttle:10,1',
            'checkout.order.store' => 'throttle:5,1',
            'checkout.order.pay' => 'throttle:5,1',
            'orders.payment.retry' => 'throttle:5,1',
        ] as $routeName => $middleware) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route);
            $this->assertContains($middleware, $route->gatherMiddleware());
        }
    }

    public function test_private_local_storage_routes_are_not_exposed(): void
    {
        $this->assertNull(Route::getRoutes()->getByName('storage.local'));
        $this->assertNull(Route::getRoutes()->getByName('storage.local.upload'));
    }

    public function test_browser_security_headers_are_added(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    private function order(?User $user, string $token): Order
    {
        return Order::query()->create([
            'user_id' => $user?->id,
            'order_code' => 'ORD-'.fake()->unique()->numerify('########'),
            'success_token' => $token,
            'customer_name' => 'Security Test',
            'customer_phone' => '0900000000',
            'customer_email' => 'security@example.com',
            'shipping_address' => 'Hanoi',
            'currency_code' => 'VND',
            'currency_symbol' => '₫',
            'currency_symbol_position' => 'after',
            'currency_decimal_places' => 0,
            'exchange_rate' => 1,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'shipping_fee' => 0,
            'total_amount' => 100000,
            'payment_method' => 'cod',
            'payment_method_name' => 'Cash on Delivery',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'placed_at' => now(),
        ]);
    }
}
