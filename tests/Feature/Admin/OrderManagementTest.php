<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_are_protected_and_customer_is_forbidden(): void
    {
        $order = $this->order();

        $this->get(route('admin.orders.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.orders.show', $order))
            ->assertForbidden();
    }

    public function test_order_list_supports_search_filters_and_pagination(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matching = $this->order(['order_code' => 'ORD-MATCH', 'customer_name' => 'Alice', 'order_status' => 'confirmed', 'payment_status' => 'unpaid', 'fulfillment_status' => 'processing']);
        $this->order(['order_code' => 'ORD-OTHER', 'customer_name' => 'Bob', 'order_status' => 'pending', 'payment_status' => 'paid']);
        for ($i = 0; $i < 16; $i++) {
            $this->order(['order_code' => 'ORD-PAGE-'.$i]);
        }

        $this->actingAs($admin)->get(route('admin.orders.index', [
            'search' => 'Alice',
            'order_status' => 'confirmed',
            'payment_status' => 'unpaid',
            'fulfillment_status' => 'processing',
            'payment_method' => 'cod',
            'customer_type' => 'guest',
        ]))->assertOk()->assertSee($matching->order_code)->assertDontSee('ORD-OTHER');

        $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk()->assertViewHas('orders', fn ($orders) => $orders->perPage() === 15 && $orders->hasPages());
    }

    public function test_order_detail_displays_full_snapshots(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order([
            'coupon_snapshot' => ['code' => 'SAVE10', 'discount_amount' => 10000],
            'tax_snapshot' => [['tax_name' => 'VAT', 'tax_rate' => 10, 'tax_amount' => 20000]],
        ]);
        $order->orderItems()->create([
            'product_name' => 'Snapshot Product', 'sku' => 'SNAP-1', 'variant_name' => 'Blue / M',
            'price' => 100000, 'quantity' => 2, 'subtotal' => 200000, 'tax_name' => 'VAT',
            'taxable_amount' => 200000, 'tax_rate' => 10, 'tax_amount' => 20000, 'total' => 220000,
        ]);
        foreach (['shipping', 'billing'] as $type) {
            $order->orderAddresses()->create([
                'type' => $type, 'full_name' => 'Snapshot Buyer', 'phone' => '0900000000',
                'country_code' => 'VN', 'province' => 'HCM', 'district' => 'D1', 'ward' => 'Ben Nghe',
                'address_line' => '1 Nguyen Hue',
            ]);
        }
        $order->orderPayments()->create([
            'payment_method_code' => 'cod', 'payment_method_name' => 'Cash on Delivery',
            'payment_status' => 'unpaid', 'amount' => 220000, 'currency_code' => 'VND',
        ]);

        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Snapshot Product')
            ->assertSee('Snapshot Buyer')
            ->assertSee('1 Nguyen Hue')
            ->assertSee('SAVE10')
            ->assertSee('VAT')
            ->assertSee('Cash on Delivery')
            ->assertSee(__('admin.orders.mark_paid_confirm'));
    }

    public function test_admin_can_only_follow_valid_status_transitions_and_history_is_recorded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order(['order_status' => 'pending']);

        $this->actingAs($admin)->patchJson(route('admin.orders.status.update', $order), [
            'status' => 'confirmed', 'note' => 'Stock checked',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertSame('confirmed', $order->fresh()->order_status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id, 'from_status' => 'pending', 'to_status' => 'confirmed',
            'note' => 'Stock checked', 'changed_by' => $admin->id,
        ]);

        $this->actingAs($admin)->patchJson(route('admin.orders.status.update', $order), ['status' => 'completed'])
            ->assertUnprocessable();
        $this->assertSame('confirmed', $order->fresh()->order_status);
    }

    public function test_admin_can_update_cod_payment_and_mark_it_paid_without_reload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order(['payment_status' => 'unpaid']);
        $order->payment()->create(['payment_method' => 'cod', 'amount' => 100000, 'currency_code' => 'VND', 'status' => 'unpaid']);
        $order->orderPayments()->create([
            'payment_method_code' => 'cod', 'payment_method_name' => 'COD', 'payment_status' => 'unpaid',
            'amount' => 100000, 'currency_code' => 'VND',
        ]);

        $this->actingAs($admin)->postJson(route('admin.orders.mark-paid', $order))
            ->assertOk()->assertJsonStructure(['status_html', 'management_html', 'timeline_html']);

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertNotNull($order->payment->fresh()->paid_at);
        $this->assertNotNull($order->orderPayments()->first()->paid_at);
        $this->assertDatabaseHas('order_payment_histories', [
            'order_id' => $order->id, 'from_status' => 'unpaid', 'to_status' => 'paid', 'changed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_update_fulfillment_only_through_valid_transitions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order(['fulfillment_status' => 'unfulfilled']);

        $this->actingAs($admin)->patchJson(route('admin.orders.fulfillment.update', $order), [
            'fulfillment_status' => 'processing', 'note' => 'Packing',
        ])->assertOk();
        $this->assertSame('processing', $order->fresh()->fulfillment_status);
        $this->assertDatabaseHas('order_notes', ['order_id' => $order->id, 'type' => 'system']);

        $this->actingAs($admin)->patchJson(route('admin.orders.fulfillment.update', $order), [
            'fulfillment_status' => 'delivered',
        ])->assertUnprocessable();
    }

    public function test_mark_paid_on_cancelled_order_and_cancel_completed_order_are_blocked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cancelled = $this->order(['order_status' => 'cancelled', 'payment_status' => 'unpaid']);
        $completed = $this->order(['order_status' => 'completed']);

        $this->actingAs($admin)->postJson(route('admin.orders.mark-paid', $cancelled))->assertUnprocessable();
        $this->actingAs($admin)->postJson(route('admin.orders.cancel', $completed), [
            'reason' => 'Too late', 'restock' => true,
        ])->assertUnprocessable();

        $this->assertSame('unpaid', $cancelled->fresh()->payment_status);
        $this->assertNull($completed->fresh()->inventory_restocked_at);
    }

    public function test_cancelling_with_restock_is_atomic_logged_and_cannot_duplicate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product();
        $stock = InventoryStock::query()->create([
            'product_id' => $product->id, 'quantity' => 7, 'reserved_quantity' => 0, 'low_stock_threshold' => 2,
        ]);
        $order = $this->order();
        $order->orderItems()->create([
            'product_id' => $product->id, 'product_name' => 'Product', 'sku' => 'ORDER-SKU',
            'price' => 100000, 'quantity' => 3, 'subtotal' => 300000, 'taxable_amount' => 300000,
            'tax_rate' => 0, 'tax_amount' => 0, 'total' => 300000,
        ]);

        $this->actingAs($admin)->postJson(route('admin.orders.cancel', $order), [
            'reason' => 'Customer requested', 'restock' => true,
        ])->assertOk();

        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertSame('cancelled', $order->fresh()->fulfillment_status);
        $this->assertNotNull($order->fresh()->inventory_restocked_at);
        $this->assertSame(10, $stock->fresh()->quantity);
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_stock_id' => $stock->id, 'type' => 'cancel_order',
            'quantity_before' => 7, 'quantity_change' => 3, 'quantity_after' => 10, 'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->postJson(route('admin.orders.cancel', $order), [
            'reason' => 'Retry', 'restock' => true,
        ])->assertUnprocessable();
        $this->assertSame(10, $stock->fresh()->quantity);
        $this->assertSame(1, $stock->inventoryLogs()->where('type', 'cancel_order')->count());
        $this->assertDatabaseHas('order_notes', ['order_id' => $order->id, 'type' => 'system']);
    }

    public function test_admin_can_add_internal_note_to_timeline(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order();

        $this->actingAs($admin)->postJson(route('admin.orders.notes.store', $order), ['note' => 'Call before delivery'])
            ->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('order_notes', ['order_id' => $order->id, 'note' => 'Call before delivery', 'created_by' => $admin->id]);

        $this->actingAs($admin)->postJson(route('admin.orders.notes.store', $order), ['note' => ''])
            ->assertUnprocessable();
    }

    private function order(array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'order_code' => 'ORD-'.fake()->unique()->numerify('######'),
            'success_token' => fake()->unique()->sha1(),
            'customer_name' => 'Buyer',
            'customer_phone' => '0900000000',
            'customer_email' => 'buyer@example.com',
            'shipping_address' => '1 Nguyen Hue',
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
        ], $overrides));
    }

    private function product(): Product
    {
        $category = Category::query()->create(['sort_order' => 0, 'status' => true]);

        return Product::query()->create([
            'category_id' => $category->id, 'sku' => 'ORDER-SKU', 'price' => 100000, 'status' => true,
        ]);
    }
}
