<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::query()->create(['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'is_default' => true, 'status' => true, 'sort_order' => 1]);
        Language::query()->create(['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => false, 'status' => true, 'sort_order' => 2]);
        Currency::query()->create(['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'exchange_rate' => 1, 'decimal_places' => 0, 'symbol_position' => 'after', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => true, 'status' => true]);
        Currency::query()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 25000, 'decimal_places' => 2, 'symbol_position' => 'before', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => false, 'status' => true]);
        Cache::flush();
    }

    public function test_guest_can_open_catalog_and_home_redirects_to_it(): void
    {
        $this->get('/')->assertRedirect('/products');
        $this->get('/products')->assertOk()->assertSee('Khám phá sản phẩm');
    }

    public function test_catalog_only_displays_active_products_from_active_categories(): void
    {
        $activeCategory = $this->category('dien-tu', 'Điện tử');
        $inactiveCategory = $this->category('an', 'Danh mục ẩn', false);
        $this->product($activeCategory, 'ACTIVE-1', 'Sản phẩm hoạt động');
        $this->product($activeCategory, 'INACTIVE-1', 'Sản phẩm ngừng bán', false);
        $this->product($inactiveCategory, 'HIDDEN-1', 'Sản phẩm danh mục ẩn');

        $this->get('/products')
            ->assertOk()
            ->assertSee('Sản phẩm hoạt động')
            ->assertDontSee('Sản phẩm ngừng bán')
            ->assertDontSee('Sản phẩm danh mục ẩn');
    }

    public function test_catalog_supports_translation_fallback_and_currency_conversion(): void
    {
        $category = $this->category('thiet-bi', 'Thiết bị');
        $product = $this->product($category, 'PHONE-1', 'Điện thoại', true, 1_000_000);
        $product->productTranslations()->create(['language_code' => 'en', 'name' => 'Smart phone', 'slug' => 'smart-phone', 'short_description' => 'Modern phone']);
        $fallback = $this->product($category, 'FALLBACK-1', 'Tên mặc định', true, 500_000);

        $this->get('/products?language=en&currency=USD')
            ->assertOk()
            ->assertSee('Smart phone')
            ->assertSee('Tên mặc định')
            ->assertSee('$40.00')
            ->assertSee('$20.00');

        $this->assertSame('en', session('storefront_language'));
        $this->assertSame('USD', session('storefront_currency'));
    }

    public function test_catalog_filters_by_keyword_category_price_and_stock(): void
    {
        $parent = $this->category('cong-nghe', 'Công nghệ');
        $child = $this->category('dien-thoai', 'Điện thoại', true, $parent->id);
        $other = $this->category('gia-dung', 'Gia dụng');
        $phone = $this->product($child, 'PHONE-RED', 'Điện thoại đỏ', true, 6_000_000);
        $this->stock($phone, 8, 1, 2);
        $expensive = $this->product($child, 'LAPTOP-1', 'Máy tính xách tay', true, 20_000_000);
        $this->stock($expensive, 0);
        $this->product($other, 'PAN-1', 'Chảo chống dính', true, 500_000);

        foreach (['keyword=PHONE', 'category=cong-nghe', 'min_price=5000000&max_price=10000000', 'stock=in_stock'] as $query) {
            $this->get('/products?'.$query)->assertSee('Điện thoại đỏ', false, "Failed filter: {$query}");
        }

        $this->get('/products?keyword=PHONE&category=cong-nghe&min_price=5000000&max_price=10000000&stock=in_stock')
            ->assertOk()
            ->assertSee('Điện thoại đỏ')
            ->assertDontSee('Máy tính xách tay')
            ->assertDontSee('Chảo chống dính');
    }

    public function test_catalog_displays_sale_image_stock_badges_and_placeholder(): void
    {
        $category = $this->category('phu-kien', 'Phụ kiện');
        $sale = $this->product($category, 'SALE-1', 'Tai nghe giảm giá', true, 1_000_000, 800_000, true);
        $this->stock($sale, 2, 0, 3);
        ProductImage::query()->create(['product_id' => $sale->id, 'image_path' => 'products/headphones.webp', 'alt_text' => 'Ảnh tai nghe', 'sort_order' => 1, 'status' => true, 'is_main' => true]);
        $plain = $this->product($category, 'PLAIN-1', 'Sản phẩm không ảnh');

        $this->get('/products')
            ->assertOk()
            ->assertSee('/storage/products/headphones.webp', false)
            ->assertSee('-20%')
            ->assertSee('Sắp hết hàng')
            ->assertSee('Chưa có ảnh')
            ->assertDontSee('cost_price');
    }

    public function test_catalog_sorts_and_paginates_twelve_products(): void
    {
        $category = $this->category('sach', 'Sách');
        foreach (range(1, 13) as $number) {
            $this->product($category, "BOOK-{$number}", "Sách {$number}", true, $number * 10_000);
        }

        $this->get('/products?sort=price_desc')
            ->assertOk()
            ->assertSeeInOrder(['Sách 13', 'Sách 12'])
            ->assertDontSee('Sách 1</h2>', false)
            ->assertSee('page=2', false);

        $this->get('/products?sort=price_desc&page=2')->assertOk()->assertSee('Sách 1');
    }

    public function test_unknown_filters_return_empty_state_and_invalid_prices_are_rejected(): void
    {
        $this->get('/products?category=khong-ton-tai')->assertOk()->assertSee('Chưa tìm thấy sản phẩm');
        $this->get('/products?min_price=100&max_price=10')->assertSessionHasErrors('max_price');
    }

    private function category(string $slug, string $name, bool $status = true, ?int $parentId = null): Category
    {
        $category = Category::query()->create(['parent_id' => $parentId, 'sort_order' => 1, 'status' => $status]);
        $category->categoryTranslations()->create(['language_code' => 'vi', 'name' => $name, 'slug' => $slug]);

        return $category;
    }

    private function product(Category $category, string $sku, string $name, bool $status = true, int $price = 100_000, ?int $salePrice = null, bool $featured = false): Product
    {
        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => $sku,
            'price' => $price,
            'sale_price' => $salePrice,
            'status' => $status,
            'is_featured' => $featured,
        ]);
        $product->productTranslations()->create(['language_code' => 'vi', 'name' => $name, 'slug' => strtolower($sku), 'short_description' => "Mô tả {$name}"]);

        return $product;
    }

    private function stock(Product $product, int $quantity, int $reserved = 0, int $threshold = 5): void
    {
        InventoryStock::query()->create(['product_id' => $product->id, 'quantity' => $quantity, 'reserved_quantity' => $reserved, 'low_stock_threshold' => $threshold]);
    }
}
