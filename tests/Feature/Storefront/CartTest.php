<?php

namespace Tests\Feature\Storefront;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Language::query()->create(['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'is_default' => true, 'status' => true, 'sort_order' => 1]);
        Currency::query()->create(['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'exchange_rate' => 1, 'decimal_places' => 0, 'symbol_position' => 'after', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => true, 'status' => true]);
        Cache::flush();
    }

    public function test_guest_can_add_update_remove_and_clear_cart_without_stock_deduction(): void
    {
        $product = $this->product();
        $stock = $this->stock($product, 5);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('cart_count', 2);

        $item = CartItem::query()->firstOrFail();
        $this->assertSame(5, $stock->refresh()->quantity);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertOk()
            ->assertJsonPath('cart_count', 3);
        $this->assertSame(3, $item->refresh()->quantity);

        $this->patchJson(route('cart.items.update', $item), ['quantity' => 4])
            ->assertOk()
            ->assertJsonPath('item_quantity', 4)
            ->assertJsonPath('cart_count', 4);

        $this->deleteJson(route('cart.items.destroy', $item))
            ->assertOk()
            ->assertJsonPath('removed_item_id', $item->id)
            ->assertJsonPath('is_empty', true)
            ->assertJsonPath('cart_count', 0);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])->assertOk();
        $this->deleteJson(route('cart.clear'))->assertOk()->assertJsonPath('cart_count', 0);
    }

    public function test_variant_product_requires_active_variant_and_stock_limit(): void
    {
        $product = $this->product();
        $variant = ProductVariant::query()->create(['product_id' => $product->id, 'sku' => 'TSHIRT-M', 'name' => 'Size M', 'status' => true]);
        $this->stock($product, 2, 0, $variant->id);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 3])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 2])
            ->assertOk()
            ->assertJsonPath('cart_count', 2);

        $variant->update(['status' => false]);
        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 1])
            ->assertUnprocessable();
    }

    public function test_cart_page_displays_variant_image_and_fallback_product_data(): void
    {
        $product = $this->product(name: 'Áo thun', slug: 'ao-thun');
        ProductImage::query()->create(['product_id' => $product->id, 'image_path' => 'products/base.webp', 'is_main' => true, 'status' => true]);
        $variant = ProductVariant::query()->create(['product_id' => $product->id, 'sku' => 'TSHIRT-BLACK', 'name' => 'Black / M', 'status' => true]);
        $variant->variantImages()->create(['image_path' => 'variant-images/black.webp', 'is_main' => true, 'status' => true]);
        $this->stock($product, 4, 0, $variant->id);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 1])->assertOk();

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Áo thun')
            ->assertSee('Black / M')
            ->assertSee('TSHIRT-BLACK')
            ->assertSee('/storage/variant-images/black.webp', false)
            ->assertSee('100,000 ₫')
            ->assertSee('data-cart-update', false)
            ->assertSee('data-cart-remove', false)
            ->assertSee('data-cart-clear', false)
            ->assertDontSee('window.confirm', false);
    }

    public function test_customer_cart_uses_user_id_and_guest_cart_merges_after_login(): void
    {
        $customer = User::factory()->create(['email' => 'customer@example.com', 'password' => bcrypt('password'), 'role' => 'customer']);
        $product = $this->product(sku: 'MERGE-1');
        $other = $this->product(sku: 'MERGE-2');
        $this->stock($product, 10);
        $this->stock($other, 10);

        $this->actingAs($customer)->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])->assertOk();
        auth()->logout();
        $this->flushSession();

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 3])->assertOk();
        $this->postJson(route('cart.items.store'), ['product_id' => $other->id, 'quantity' => 1])->assertOk();

        $this->post(route('login'), ['email' => 'customer@example.com', 'password' => 'password'])->assertRedirect(route('account.index'));

        $cart = Cart::query()->where('user_id', $customer->id)->where('status', 'active')->with('cartItems')->firstOrFail();
        $this->assertSame(2, $cart->cartItems->count());
        $this->assertSame(5, $cart->cartItems->firstWhere('product_id', $product->id)->quantity);
        $this->assertSame(1, $cart->cartItems->firstWhere('product_id', $other->id)->quantity);
    }

    public function test_header_badge_and_product_detail_add_to_cart_are_enabled(): void
    {
        $product = $this->product(name: 'Máy ảnh', slug: 'may-anh');
        $this->stock($product, 3);
        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])->assertOk();

        $this->get('/products/may-anh')
            ->assertOk()
            ->assertSee('data-cart-count', false)
            ->assertSee('data-cart-add', false)
            ->assertSee(route('cart.items.store'), false)
            ->assertDontSee('Tính năng giỏ hàng sẽ được kích hoạt trong Task 15.');
    }

    private function product(string $sku = 'SKU-1', string $name = 'Sản phẩm', string $slug = 'san-pham', bool $status = true): Product
    {
        $category = Category::query()->first() ?? Category::query()->create(['sort_order' => 1, 'status' => true]);
        if (! $category->categoryTranslations()->exists()) {
            $category->categoryTranslations()->create(['language_code' => 'vi', 'name' => 'Danh mục', 'slug' => 'danh-muc']);
        }
        $product = Product::query()->create(['category_id' => $category->id, 'sku' => $sku, 'price' => 100_000, 'sale_price' => null, 'status' => $status]);
        $resolvedSlug = ProductTranslation::query()->where('language_code', 'vi')->where('slug', $slug)->exists()
            ? $slug.'-'.$product->id
            : $slug;
        $product->productTranslations()->create(['language_code' => 'vi', 'name' => $name, 'slug' => $resolvedSlug, 'short_description' => 'Mô tả']);

        return $product;
    }

    private function stock(Product $product, int $quantity, int $reserved = 0, ?int $variantId = null): InventoryStock
    {
        return InventoryStock::query()->create(['product_id' => $product->id, 'product_variant_id' => $variantId, 'quantity' => $quantity, 'reserved_quantity' => $reserved, 'low_stock_threshold' => 2]);
    }
}
