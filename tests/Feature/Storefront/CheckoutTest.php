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
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Product;
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
        $this->assertSame(10.0, (float) $session->tax_snapshot[0]['tax_rate']);
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

    public function test_guest_can_complete_cod_order_idempotently_and_checkout_token_is_owner_scoped(): void
    {
        $product = $this->product();
        $stock = InventoryStock::query()->create([
            'product_id' => $product->id,
            'quantity' => 5,
            'reserved_quantity' => 0,
            'low_stock_threshold' => 1,
        ]);
        $coupon = Coupon::query()->create([
            'code' => 'E2E10',
            'discount_type' => Coupon::TYPE_FIXED_AMOUNT,
            'discount_value' => 10_000,
            'status' => Coupon::STATUS_ACTIVE,
        ]);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])->assertOk();
        $this->postJson(route('cart.coupon.apply'), ['code' => $coupon->code])->assertOk();
        $this->postJson(route('checkout.store'), $this->checkoutPayload())->assertOk();

        $checkout = CheckoutSession::query()->firstOrFail();

        $this->postJson(route('checkout.payment.cod', $checkout->token))
            ->assertOk()
            ->assertJsonPath('payment.payment_method_code', 'cod')
            ->assertJsonPath('ready_to_order', true);

        $first = $this->postJson(route('checkout.order.store', $checkout->token))
            ->assertOk()
            ->assertJsonPath('success', true);
        $second = $this->postJson(route('checkout.order.store', $checkout->token))
            ->assertOk()
            ->assertJsonPath('order.order_code', $first->json('order.order_code'));

        $order = Order::query()->with(['orderItems', 'orderPayments', 'payment'])->firstOrFail();
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame(1, $order->orderItems->count());
        $this->assertSame(1, $order->orderPayments->count());
        $this->assertSame('pending', $order->payment->status);
        $this->assertSame(3, $stock->refresh()->quantity);
        $this->assertSame(1, $stock->inventoryLogs()->where('type', 'order_confirmed')->count());
        $this->assertSame(1, $coupon->usages()->where('order_id', $order->id)->count());
        $this->assertSame(1, Order::query()->count());

        $this->withSession(['cart_session_id' => 'another-guest-session'])
            ->postJson(route('checkout.order.store', $checkout->token))
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_guest_can_complete_online_mock_payment_flow(): void
    {
        PaymentMethod::query()->create([
            'code' => 'online',
            'name' => 'Mock Pay',
            'gateway_code' => 'mock',
            'environment' => 'sandbox',
            'credentials' => ['secret_key' => 'task26-mock-secret'],
            'sort_order' => 20,
            'status' => 'active',
        ]);
        $product = $this->product();
        InventoryStock::query()->create([
            'product_id' => $product->id,
            'quantity' => 5,
            'reserved_quantity' => 0,
            'low_stock_threshold' => 1,
        ]);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])->assertOk();
        $this->postJson(route('checkout.store'), $this->checkoutPayload())->assertOk();
        $checkout = CheckoutSession::query()->firstOrFail();

        $this->postJson(route('checkout.payment.online', $checkout->token))
            ->assertOk()
            ->assertJsonPath('payment_method_code', 'online');

        $paymentResponse = $this->postJson(route('checkout.order.pay', $checkout->token))
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->get($paymentResponse->json('redirect_url'))
            ->assertOk()
            ->assertSee('Mock Pay');

        $transaction = PaymentTransaction::query()->firstOrFail();
        $gatewayResponse = $this->get(route('payment.mock.complete', [
            'transaction' => $transaction,
            'status' => 'paid',
            'signature' => $transaction->request_payload['signature'],
        ]))->assertRedirect();
        $this->get($gatewayResponse->headers->get('Location'))->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame('online', $order->payment_method);
        $this->assertSame('paid', $order->refresh()->payment_status);
        $this->assertSame('paid', $transaction->refresh()->status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($transaction->paid_at);
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
