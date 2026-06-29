<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductDetailTest extends TestCase
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

    public function test_guest_can_view_modern_product_detail_with_translation_price_and_seo(): void
    {
        $category = $this->category();
        $product = $this->product($category, 'Điện thoại cao cấp', 'dien-thoai-cao-cap', 1_000_000, 800_000);
        $product->productTranslations()->create([
            'language_code' => 'en', 'name' => 'Premium phone', 'slug' => 'premium-phone',
            'short_description' => 'A modern phone', 'description' => 'Full English description',
            'meta_title' => 'Premium Phone SEO', 'meta_description' => 'SEO description for phone',
        ]);
        $this->stock($product, 10, 2, 3);

        $this->get('/products/premium-phone?language=en&currency=USD')
            ->assertOk()
            ->assertSee('<title>Premium Phone SEO', false)
            ->assertSee('SEO description for phone')
            ->assertSee('Premium phone')
            ->assertSee('A modern phone')
            ->assertSee('Full English description')
            ->assertSee('$32.00')
            ->assertSee('$40.00')
            ->assertSee('PHONE-1')
            ->assertDontSee('cost_price');
    }

    public function test_detail_displays_active_gallery_and_placeholder_when_images_are_missing(): void
    {
        $category = $this->category();
        $product = $this->product($category, 'Máy ảnh', 'may-anh');
        ProductImage::query()->create(['product_id' => $product->id, 'image_path' => 'products/second.webp', 'alt_text' => 'Ảnh phụ', 'sort_order' => 2, 'status' => true, 'is_main' => false]);
        ProductImage::query()->create(['product_id' => $product->id, 'image_path' => 'products/main.webp', 'alt_text' => 'Ảnh chính', 'sort_order' => 5, 'status' => true, 'is_main' => true]);
        ProductImage::query()->create(['product_id' => $product->id, 'image_path' => 'products/hidden.webp', 'alt_text' => 'Ảnh ẩn', 'sort_order' => 1, 'status' => false, 'is_main' => false]);

        $this->get('/products/may-anh')
            ->assertOk()
            ->assertSeeInOrder(['main.webp', 'second.webp'], false)
            ->assertDontSee('hidden.webp');

        $noImage = $this->product($category, 'Không ảnh', 'khong-anh');
        $this->get('/products/khong-anh')->assertOk()->assertSee('Chưa có ảnh');
    }

    public function test_active_variants_update_price_sku_and_stock_data_while_inactive_variant_is_hidden(): void
    {
        $category = $this->category();
        $product = $this->product($category, 'Áo khoác', 'ao-khoac', 1_000_000, 900_000);
        $medium = ProductVariant::query()->create(['product_id' => $product->id, 'sku' => 'JACKET-M', 'name' => 'Size M', 'price' => null, 'sale_price' => null, 'status' => true]);
        $large = ProductVariant::query()->create(['product_id' => $product->id, 'sku' => 'JACKET-L', 'name' => 'Size L', 'price' => 1_200_000, 'sale_price' => 1_000_000, 'status' => true]);
        ProductVariant::query()->create(['product_id' => $product->id, 'sku' => 'JACKET-OLD', 'name' => 'Biến thể ẩn', 'status' => false]);
        $this->stock($product, 2, 0, 3, $medium->id);
        $this->stock($product, 0, 0, 3, $large->id);

        $response = $this->get('/products/ao-khoac');

        $response->assertOk()
            ->assertSee('Size M')
            ->assertSee('JACKET-M')
            ->assertSee('Size L')
            ->assertSee('JACKET-L')
            ->assertDontSee('Biến thể ẩn')
            ->assertDontSee('JACKET-OLD')
            ->assertSee('Vui lòng chọn một biến thể')
            ->assertSee('data-cart-add', false)
            ->assertDontSee('Tính năng giỏ hàng sẽ được kích hoạt trong Task 15.')
            ->assertViewHas('variantOptions', fn (array $variants): bool => $variants[0]['price'] === '900,000 ₫'
                && $variants[0]['stock_status'] === 'low_stock'
                && $variants[1]['price'] === '1,000,000 ₫'
                && $variants[1]['stock_status'] === 'out_of_stock');
    }

    public function test_inactive_missing_or_wrong_language_slug_returns_not_found(): void
    {
        $category = $this->category();
        $inactive = $this->product($category, 'Sản phẩm ẩn', 'san-pham-an', status: false);
        $englishOnlySlug = $this->product($category, 'Sản phẩm Việt', 'san-pham-viet');
        $englishOnlySlug->productTranslations()->create(['language_code' => 'en', 'name' => 'English Product', 'slug' => 'english-product']);

        $this->get('/products/khong-ton-tai')->assertNotFound();
        $this->get('/products/san-pham-an')->assertNotFound();
        $this->get('/products/english-product?language=vi')->assertNotFound();
        $this->get('/products/english-product?language=en')->assertOk();

        $inactive->forceDelete();
    }

    public function test_product_in_inactive_category_returns_not_found(): void
    {
        $category = $this->category(false);
        $this->product($category, 'Sản phẩm danh mục ẩn', 'danh-muc-an');

        $this->get('/products/danh-muc-an')->assertNotFound();
    }

    public function test_related_products_are_limited_to_same_active_category_and_link_to_detail(): void
    {
        $category = $this->category();
        $otherCategory = $this->category(true, 'khac', 'Khác');
        $product = $this->product($category, 'Sản phẩm chính', 'san-pham-chinh');
        $this->product($category, 'Sản phẩm liên quan', 'san-pham-lien-quan');
        $this->product($otherCategory, 'Không liên quan', 'khong-lien-quan');

        $this->get('/products/san-pham-chinh')
            ->assertOk()
            ->assertSee('Sản phẩm liên quan')
            ->assertSee('/products/san-pham-lien-quan?language=vi&amp;currency=VND', false)
            ->assertDontSee('Không liên quan');
    }

    public function test_catalog_cards_now_link_to_product_detail(): void
    {
        $category = $this->category();
        $this->product($category, 'Sản phẩm link', 'san-pham-link');

        $this->get('/products')
            ->assertOk()
            ->assertSee('/products/san-pham-link?language=vi&amp;currency=VND', false);
    }

    private function category(bool $status = true, string $slug = 'dien-tu', string $name = 'Điện tử'): Category
    {
        $category = Category::query()->create(['sort_order' => 1, 'status' => $status]);
        $category->categoryTranslations()->create(['language_code' => 'vi', 'name' => $name, 'slug' => $slug]);
        $category->categoryTranslations()->create(['language_code' => 'en', 'name' => 'Electronics '.$category->id, 'slug' => 'electronics-'.$category->id]);

        return $category;
    }

    private function product(Category $category, string $name, string $slug, int $price = 1_000_000, ?int $salePrice = null, bool $status = true): Product
    {
        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => 'PHONE-'.$category->id.'-'.Product::query()->count(),
            'price' => $price,
            'sale_price' => $salePrice,
            'cost_price' => 100,
            'status' => $status,
            'is_featured' => true,
        ]);
        $product->productTranslations()->create([
            'language_code' => 'vi', 'name' => $name, 'slug' => $slug,
            'short_description' => 'Mô tả ngắn', 'description' => 'Mô tả đầy đủ',
            'meta_title' => 'SEO '.$name, 'meta_description' => 'SEO description',
        ]);

        return $product;
    }

    private function stock(Product $product, int $quantity, int $reserved = 0, int $threshold = 5, ?int $variantId = null): void
    {
        InventoryStock::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
            'reserved_quantity' => $reserved,
            'low_stock_threshold' => $threshold,
        ]);
    }
}
