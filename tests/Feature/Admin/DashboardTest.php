<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Language;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Currency::query()->create([
            'code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫',
            'exchange_rate' => 1, 'decimal_places' => 0, 'symbol_position' => 'after',
            'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => true, 'status' => true,
        ]);
        Language::query()->create([
            'code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt',
            'is_default' => true, 'status' => true, 'sort_order' => 0,
        ]);
    }

    public function test_dashboard_routes_are_protected_and_admin_root_redirects_to_dashboard(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/dashboard')->assertRedirect(route('login'));

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin/dashboard')->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin')->assertRedirect('/admin/dashboard');
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    }

    public function test_dashboard_handles_empty_database_with_zero_and_empty_states(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('admin.dashboard.total_revenue'))
            ->assertSee(__('admin.dashboard.total_orders'))
            ->assertSee(__('admin.dashboard.pending_orders'))
            ->assertSee(__('admin.dashboard.unpaid_cod'))
            ->assertSee(__('admin.dashboard.no_orders'))
            ->assertSee(__('admin.dashboard.no_low_stock'))
            ->assertSee(__('admin.dashboard.no_top_products'));
    }

    public function test_dashboard_calculates_kpis_summaries_widgets_and_excludes_cancelled_revenue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product();
        InventoryStock::query()->create([
            'product_id' => $product->id, 'quantity' => 3, 'reserved_quantity' => 0, 'low_stock_threshold' => 5,
        ]);

        $pending = $this->order([
            'order_code' => 'ORD-DASH-PENDING', 'order_status' => 'pending',
            'payment_status' => 'unpaid', 'total_amount' => 200000,
        ]);
        $pending->orderItems()->create([
            'product_id' => $product->id, 'product_name' => 'Dashboard Product', 'sku' => 'DASH-1',
            'price' => 100000, 'quantity' => 2, 'subtotal' => 200000, 'taxable_amount' => 200000,
            'tax_rate' => 0, 'tax_amount' => 0, 'total' => 200000,
        ]);
        $this->order([
            'order_code' => 'ORD-DASH-CANCELLED', 'order_status' => 'cancelled',
            'payment_status' => 'cancelled', 'total_amount' => 900000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['range' => 'last7']));

        $response->assertOk()
            ->assertSee('200,000 ₫')
            ->assertSee('ORD-DASH-PENDING')
            ->assertSee('Dashboard Product')
            ->assertSee(__('admin.dashboard.alert_pending', ['count' => 1]))
            ->assertSee(__('admin.dashboard.alert_cod', ['count' => 1]))
            ->assertSee(__('admin.dashboard.alert_low_stock', ['count' => 1]))
            ->assertViewHas('kpis', fn (array $kpis): bool => $kpis['orders'] === 2
                && $kpis['pending_orders'] === 1
                && $kpis['unpaid_cod_orders'] === 1
                && $kpis['products'] === 1
                && $kpis['low_stock'] === 1)
            ->assertViewHas('topProducts', fn ($products): bool => (int) $products->first()->quantity_sold === 2);
    }

    public function test_today_range_excludes_older_orders_and_invalid_range_falls_back(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->order(['order_code' => 'ORD-TODAY']);
        $this->order(['order_code' => 'ORD-OLD', 'placed_at' => now()->subDays(2), 'created_at' => now()->subDays(2)]);

        $this->actingAs($admin)->get(route('admin.dashboard', ['range' => 'today']))
            ->assertOk()
            ->assertSee('ORD-TODAY')
            ->assertDontSee('ORD-OLD')
            ->assertViewHas('kpis', fn (array $kpis): bool => $kpis['orders'] === 1);

        $this->actingAs($admin)->get(route('admin.dashboard', ['range' => 'invalid']))
            ->assertOk()
            ->assertViewHas('range', 'last7');
    }

    private function order(array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'order_code' => 'ORD-'.fake()->unique()->numerify('######'),
            'customer_name' => 'Dashboard Buyer', 'customer_phone' => '0900000000',
            'customer_email' => 'dashboard@example.com', 'shipping_address' => 'Hanoi',
            'currency_code' => 'VND', 'exchange_rate' => 1, 'subtotal' => 100000,
            'discount_amount' => 0, 'tax_amount' => 0, 'shipping_fee' => 0, 'total_amount' => 100000,
            'payment_method' => 'cod', 'payment_status' => 'paid', 'order_status' => 'completed',
            'fulfillment_status' => 'delivered', 'placed_at' => now(),
        ], $overrides));
    }

    private function product(): Product
    {
        $category = Category::query()->create(['sort_order' => 0, 'status' => true]);
        $product = Product::query()->create([
            'category_id' => $category->id, 'sku' => 'DASH-1', 'price' => 100000, 'status' => true,
        ]);
        $product->productTranslations()->create([
            'language_code' => 'vi', 'name' => 'Dashboard Product', 'slug' => 'dashboard-product',
        ]);

        return $product;
    }
}
