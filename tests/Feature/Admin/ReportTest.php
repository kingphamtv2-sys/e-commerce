<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Language;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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

    public function test_report_routes_and_exports_are_admin_protected(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
        $this->get(route('admin.reports.export', ['report' => 'sales']))->assertRedirect(route('login'));

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get(route('admin.reports.sales'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee(__('admin.reports.sales_title'))
            ->assertSee(__('admin.reports.payments_title'));
    }

    public function test_sales_report_uses_order_snapshots_and_excludes_cancelled_revenue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->order([
            'order_code' => 'ORD-SALES-PAID', 'total_amount' => 220000, 'subtotal' => 200000,
            'discount_amount' => 10000, 'tax_amount' => 20000, 'shipping_fee' => 10000, 'payment_status' => 'paid',
        ]);
        $this->order([
            'order_code' => 'ORD-SALES-UNPAID', 'total_amount' => 100000, 'payment_status' => 'unpaid',
        ]);
        $this->order([
            'order_code' => 'ORD-SALES-CANCELLED', 'total_amount' => 900000,
            'order_status' => 'cancelled', 'payment_status' => 'cancelled',
        ]);

        $this->actingAs($admin)->get(route('admin.reports.sales', $this->dates()))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary): bool => (float) $summary->gross_revenue === 320000.0
                && (float) $summary->paid_revenue === 220000.0
                && (int) $summary->total_orders === 2)
            ->assertSee('320,000 ₫')
            ->assertDontSee('1,220,000');
    }

    public function test_product_and_tax_reports_use_order_item_snapshots(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product('Current Product', 'CURRENT-SKU');
        $order = $this->order();
        $order->orderItems()->create([
            'product_id' => $product->id,
            'product_name' => 'Purchased Product Snapshot',
            'sku' => 'SNAPSHOT-SKU',
            'variant_name' => 'Blue / M',
            'price' => 100000,
            'quantity' => 2,
            'subtotal' => 200000,
            'tax_name' => 'VAT Snapshot',
            'taxable_amount' => 200000,
            'tax_rate' => 10,
            'tax_amount' => 20000,
            'total' => 220000,
        ]);
        $product->update(['price' => 999999, 'sku' => 'CHANGED-SKU']);

        $this->actingAs($admin)->get(route('admin.reports.product-sales', $this->dates()))
            ->assertOk()
            ->assertSee('Purchased Product Snapshot')
            ->assertSee('SNAPSHOT-SKU')
            ->assertSee('220,000 ₫')
            ->assertDontSee('999,999');

        $this->actingAs($admin)->get(route('admin.reports.taxes', $this->dates()))
            ->assertOk()
            ->assertSee('VAT Snapshot')
            ->assertSee('20,000 ₫');
    }

    public function test_coupon_report_only_counts_persisted_order_usages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coupon = Coupon::query()->create([
            'code' => 'SAVE10', 'name' => 'Save Ten', 'discount_type' => 'percentage',
            'discount_value' => 10, 'used_count' => 1, 'status' => 'active',
        ]);
        $order = $this->order(['discount_amount' => 10000, 'coupon_snapshot' => ['code' => 'SAVE10']]);
        $coupon->usages()->create([
            'order_id' => $order->id, 'coupon_code' => 'SAVE10',
            'discount_amount' => 10000, 'used_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.reports.coupons', $this->dates()))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary): bool => (int) $summary->total_usages === 1)
            ->assertSee('SAVE10')
            ->assertSee('10,000 ₫');
    }

    public function test_inventory_and_payment_reports_highlight_operational_risks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $low = $this->product('Low Stock Product', 'LOW-1');
        $out = $this->product('Out Stock Product', 'OUT-1');
        InventoryStock::query()->create([
            'product_id' => $low->id, 'quantity' => 3, 'reserved_quantity' => 0, 'low_stock_threshold' => 5,
        ]);
        InventoryStock::query()->create([
            'product_id' => $out->id, 'quantity' => 0, 'reserved_quantity' => 0, 'low_stock_threshold' => 5,
        ]);
        $order = $this->order(['payment_status' => 'unpaid', 'total_amount' => 300000]);
        $order->orderPayments()->create([
            'payment_method_code' => 'cod', 'payment_method_name' => 'Cash on Delivery',
            'payment_status' => 'unpaid', 'amount' => 300000, 'currency_code' => 'VND',
        ]);

        $this->actingAs($admin)->get(route('admin.reports.inventory', $this->dates()))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary): bool => (int) $summary->low_stock === 1 && (int) $summary->out_of_stock === 1)
            ->assertSee('Low Stock Product')
            ->assertSee('Out Stock Product');

        $this->actingAs($admin)->get(route('admin.reports.payments', $this->dates()))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary): bool => (float) $summary->cod_unpaid_amount === 300000.0)
            ->assertSee(__('admin.reports.cod_needs_collection'))
            ->assertSee('300,000 ₫');
    }

    public function test_csv_export_uses_current_filters_and_is_not_paginated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->order(['order_code' => 'ORD-INCLUDED', 'order_status' => 'completed']);
        $this->order(['order_code' => 'ORD-EXCLUDED', 'order_status' => 'pending']);

        $response = $this->actingAs($admin)->get(route('admin.reports.export', [
            'report' => 'orders',
            ...$this->dates(),
            'order_status' => 'completed',
        ]));

        $response->assertOk()->assertDownload();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('ORD-INCLUDED', $csv);
        $this->assertStringNotContainsString('ORD-EXCLUDED', $csv);
    }

    private function dates(): array
    {
        return ['date_from' => now()->subDay()->toDateString(), 'date_to' => now()->addDay()->toDateString()];
    }

    private function order(array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'order_code' => 'ORD-'.fake()->unique()->numerify('######'),
            'customer_name' => 'Report Buyer', 'customer_phone' => '0900000000',
            'customer_email' => 'report@example.com', 'shipping_address' => 'Hanoi',
            'currency_code' => 'VND', 'currency_symbol' => '₫', 'currency_symbol_position' => 'after',
            'currency_decimal_places' => 0, 'exchange_rate' => 1, 'subtotal' => 100000,
            'discount_amount' => 0, 'tax_amount' => 0, 'shipping_fee' => 0, 'total_amount' => 100000,
            'payment_method' => 'cod', 'payment_method_name' => 'Cash on Delivery',
            'payment_status' => 'paid', 'order_status' => 'completed',
            'fulfillment_status' => 'delivered', 'placed_at' => now(),
        ], $overrides));
    }

    private function product(string $name, string $sku): Product
    {
        $category = Category::query()->create(['sort_order' => 0, 'status' => true]);
        $category->categoryTranslations()->create([
            'language_code' => 'vi', 'name' => "{$name} Category", 'slug' => strtolower(str_replace(' ', '-', $name)).'-category',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id, 'sku' => $sku, 'price' => 100000, 'status' => true,
        ]);
        $product->productTranslations()->create([
            'language_code' => 'vi', 'name' => $name, 'slug' => strtolower(str_replace(' ', '-', $name)),
        ]);

        return $product;
    }
}
