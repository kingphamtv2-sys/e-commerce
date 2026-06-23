<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\CheckoutSession;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Language;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\SystemSetting;
use App\Models\TaxClass;
use App\Models\TaxRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::query()->create(['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'is_default' => true, 'status' => true, 'sort_order' => 1]);
        Currency::query()->create(['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'exchange_rate' => 1, 'decimal_places' => 0, 'symbol_position' => 'after', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => true, 'status' => true]);
        SystemSetting::query()->create(['key' => 'tax_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'tax', 'is_public' => true]);
        SystemSetting::query()->create(['key' => 'price_include_tax', 'value' => '0', 'type' => 'boolean', 'group' => 'tax', 'is_public' => true]);
        Cache::flush();
    }

    public function test_empty_cart_cannot_checkout(): void
    {
        $this->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('checkout');

        $this->postJson(route('checkout.store'), $this->checkoutPayload())
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_guest_checkout_creates_draft_session_with_snapshots_only(): void
    {
        $taxClass = TaxClass::query()->create(['code' => 'standard_tax', 'name' => 'Standard Tax', 'status' => true]);
        TaxRate::query()->create(['tax_class_id' => $taxClass->id, 'country_code' => 'VN', 'rate' => 10, 'priority' => 1, 'status' => true]);
        $product = $this->product($taxClass);
        $stock = InventoryStock::query()->create(['product_id' => $product->id, 'quantity' => 5, 'reserved_quantity' => 0, 'low_stock_threshold' => 1]);
        Coupon::query()->create([
            'code' => 'SAVE10',
            'discount_type' => Coupon::TYPE_FIXED_AMOUNT,
            'discount_value' => 10_000,
            'status' => Coupon::STATUS_ACTIVE,
        ]);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])->assertOk();
        $this->postJson(route('cart.coupon.apply'), ['code' => 'SAVE10'])->assertOk();

        $this->postJson(route('checkout.store'), $this->checkoutPayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('summary.subtotal', 200000)
            ->assertJsonPath('summary.discount_amount', 10000)
            ->assertJsonPath('summary.tax_amount', 19000)
            ->assertJsonPath('summary.grand_total', 209000)
            ->assertJsonStructure(['checkout_session' => ['token', 'expires_at']]);

        $session = CheckoutSession::query()->firstOrFail();
        $this->assertGreaterThanOrEqual(60, strlen($session->token));
        $this->assertSame('VND', $session->currency_snapshot['code']);
        $this->assertSame('₫', $session->currency_snapshot['symbol']);
        $this->assertSame('SAVE10', $session->coupon_snapshot['code']);
        $this->assertSame('Standard Tax', $session->tax_snapshot[0]['tax_name']);
        $this->assertSame(10.0, $session->tax_snapshot[0]['tax_rate']);
        $this->assertSame('Checkout Product', $session->items_snapshot[0]['product_name']);
        $this->assertSame('CHECKOUT-1', $session->items_snapshot[0]['sku']);
        $this->assertSame(2, $session->items_snapshot[0]['quantity']);
        $this->assertSame(5, $stock->refresh()->quantity);
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(0, Payment::query()->count());
    }

    public function test_checkout_rejects_quantity_over_available_stock(): void
    {
        $product = $this->product();
        InventoryStock::query()->create(['product_id' => $product->id, 'quantity' => 2, 'reserved_quantity' => 0, 'low_stock_threshold' => 1]);
        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])->assertOk();
        InventoryStock::query()->where('product_id', $product->id)->update(['quantity' => 1]);

        $this->postJson(route('checkout.store'), $this->checkoutPayload())
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    private function product(?TaxClass $taxClass = null): Product
    {
        $category = Category::query()->create(['sort_order' => 1, 'status' => true]);
        $category->categoryTranslations()->create(['language_code' => 'vi', 'name' => 'Danh mục', 'slug' => 'danh-muc']);
        $product = Product::query()->create(['category_id' => $category->id, 'tax_class_id' => $taxClass?->id, 'sku' => 'CHECKOUT-1', 'price' => 100_000, 'status' => true]);
        $product->productTranslations()->create(['language_code' => 'vi', 'name' => 'Checkout Product', 'slug' => 'checkout-product']);

        return $product;
    }

    private function checkoutPayload(): array
    {
        return [
            'contact' => ['name' => 'Guest Buyer', 'email' => 'guest@example.com', 'phone' => '0900000000'],
            'shipping' => [
                'full_name' => 'Guest Buyer',
                'phone' => '0900000000',
                'country_code' => 'VN',
                'province' => 'Ho Chi Minh',
                'district' => 'District 1',
                'ward' => 'Ben Nghe',
                'address_line' => '1 Nguyen Hue',
            ],
            'billing_same_as_shipping' => true,
        ];
    }
}
