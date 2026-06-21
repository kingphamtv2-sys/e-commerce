<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\TaxClass;
use App\Models\User;
use App\Services\ProductService;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\TaxClassSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private TaxClass $taxClass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, CurrencySeeder::class, TaxClassSeeder::class]);
        $this->category = Category::query()->create(['sort_order' => 0, 'status' => true]);
        $this->category->categoryTranslations()->create(['language_code' => 'vi', 'name' => 'Áo nam', 'slug' => 'ao-nam']);
        $this->taxClass = TaxClass::query()->where('code', 'standard_tax')->firstOrFail();
    }

    public function test_admin_can_view_product_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();

        $this->actingAs($admin)->get('/admin/products')->assertOk()->assertSee('Áo thun đen')->assertSee('TSHIRT-001');
        $this->actingAs($admin)->get('/admin/products/create')->assertOk()->assertSee('Tiếng Việt');
        $this->actingAs($admin)->get(route('admin.products.edit', $product))->assertOk()->assertSee('TSHIRT-001');
    }

    public function test_product_edit_uses_collision_safe_keys_for_dynamic_variant_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();
        $variant = $product->productVariants()->create([
            'sku' => 'TSHIRT-001-M', 'name' => 'Size M', 'status' => true,
        ]);

        $this->actingAs($admin)->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('existing-'.$variant->id)
            ->assertSee(':key="variant._key"', false)
            ->assertDontSee('variant.id || index', false);
    }

    public function test_guest_and_customer_cannot_access_products(): void
    {
        $this->get('/admin/products')->assertRedirect(route('login'));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin/products')->assertForbidden();
    }

    public function test_admin_can_create_product_with_translations_and_variants(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Cache::put(ProductService::CACHE_ALL, ['stale']);

        $this->actingAs($admin)->post('/admin/products', $this->payload())
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('sku', 'TSHIRT-NEW')->firstOrFail();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => $this->category->id, 'tax_class_id' => $this->taxClass->id, 'price' => 200000, 'sale_price' => 180000, 'is_featured' => 1]);
        $this->assertDatabaseHas('product_translations', ['product_id' => $product->id, 'language_code' => 'vi', 'slug' => 'ao-thun-nam-mau-den']);
        $this->assertDatabaseHas('product_translations', ['product_id' => $product->id, 'language_code' => 'en', 'slug' => 'black-men-t-shirt']);
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'TSHIRT-NEW-M', 'name' => 'Size M', 'price' => null]);
        $this->assertTrue(Cache::missing(ProductService::CACHE_ALL));
    }

    public function test_product_requires_default_translation_and_valid_prices(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = $this->payload();
        $payload['price'] = -1;
        $payload['sale_price'] = 300000;
        $payload['translations']['vi']['name'] = '';
        $payload['translations']['vi']['slug'] = '';

        $this->actingAs($admin)->post('/admin/products', $payload)
            ->assertSessionHasErrors(['price', 'sale_price', 'translations.vi.name']);
    }

    public function test_product_sku_and_translation_slug_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createProduct();
        $payload = $this->payload();
        $payload['sku'] = 'TSHIRT-001';
        $payload['translations']['vi']['slug'] = 'ao-thun-den';

        $this->actingAs($admin)->post('/admin/products', $payload)
            ->assertSessionHasErrors(['sku', 'translations.vi.slug']);
    }

    public function test_variant_skus_are_unique_and_cannot_match_product_sku(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->payload();
        $payload['variants'] = [
            ['sku' => 'TSHIRT-NEW', 'name' => 'Size M', 'status' => '1'],
            ['sku' => 'TSHIRT-NEW', 'name' => 'Size L', 'status' => '1'],
        ];

        $this->actingAs($admin)->post('/admin/products', $payload)
            ->assertSessionHasErrors(['variants.0.sku', 'variants.1.sku']);
    }

    public function test_variant_sale_price_cannot_exceed_effective_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->payload();
        $payload['variants'][0]['price'] = 210000;
        $payload['variants'][0]['sale_price'] = 220000;

        $this->actingAs($admin)->post('/admin/products', $payload)
            ->assertSessionHasErrors('variants.0.sale_price');
    }

    public function test_inactive_category_and_tax_class_cannot_be_used_for_new_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->category->update(['status' => false]);
        $this->taxClass->update(['status' => false]);

        $this->actingAs($admin)->post('/admin/products', $this->payload())
            ->assertSessionHasErrors(['category_id', 'tax_class_id']);
    }

    public function test_admin_can_update_product_translations_and_sync_variants(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();
        $removed = $product->productVariants()->create(['sku' => 'TSHIRT-001-M', 'name' => 'Size M', 'status' => true]);
        $kept = $product->productVariants()->create(['sku' => 'TSHIRT-001-L', 'name' => 'Size L', 'status' => true]);
        $payload = $this->payload();
        $payload['sku'] = 'TSHIRT-001';
        $payload['status'] = '0';
        $payload['translations']['vi'] = ['name' => 'Áo thun cập nhật', 'slug' => 'ao-thun-cap-nhat'];
        $payload['translations']['en'] = ['name' => '', 'slug' => '', 'short_description' => '', 'description' => '', 'meta_title' => '', 'meta_description' => ''];
        $payload['variants'] = [
            ['id' => $kept->id, 'sku' => 'TSHIRT-001-L', 'name' => 'Large', 'price' => 210000, 'sale_price' => 190000, 'status' => '1'],
            ['sku' => 'TSHIRT-001-XL', 'name' => 'Extra Large', 'price' => '', 'sale_price' => '', 'status' => '1'],
        ];

        $this->actingAs($admin)->put(route('admin.products.update', $product), $payload)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 0]);
        $this->assertDatabaseHas('product_variants', ['id' => $kept->id, 'name' => 'Large', 'price' => 210000]);
        $this->assertSoftDeleted('product_variants', ['id' => $removed->id]);
        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'TSHIRT-001-XL']);
        $this->assertDatabaseMissing('product_translations', ['product_id' => $product->id, 'language_code' => 'en']);
    }

    public function test_product_translation_falls_back_to_default_then_first_available(): void
    {
        $product = $this->createProduct();
        $product->productTranslations()->create(['language_code' => 'en', 'name' => 'Black T-Shirt', 'slug' => 'black-t-shirt']);
        $service = app(ProductService::class);

        $this->assertSame('Black T-Shirt', $service->translation($product, 'en')?->name);
        $this->assertSame('Áo thun đen', $service->translation($product, 'ja')?->name);
        $product->productTranslations()->where('language_code', 'vi')->delete();
        $product->unsetRelation('productTranslations');
        $this->assertSame('Black T-Shirt', $service->translation($product, 'ja')?->name);
    }

    public function test_product_list_supports_filters_and_price_sorting(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $match = $this->createProduct();
        $match->update(['is_featured' => true]);
        $other = $this->createProduct('OTHER-001', 'Sản phẩm khác', 'san-pham-khac');
        $other->update(['status' => false]);
        app(ProductService::class)->clearCache();

        $this->actingAs($admin)->get('/admin/products?keyword=TSHIRT&category_id='.$this->category->id.'&status=1&is_featured=1&sort=price_asc')
            ->assertOk()->assertSee('TSHIRT-001')->assertDontSee('OTHER-001');
    }

    public function test_product_all_filters_do_not_apply_null_filter_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $active = $this->createProduct();
        $inactive = $this->createProduct('OTHER-001', 'Sản phẩm tạm dừng', 'san-pham-tam-dung');
        $inactive->update(['status' => false, 'is_featured' => true]);
        app(ProductService::class)->clearCache();

        $this->actingAs($admin)->get('/admin/products?keyword=&category_id=&status=&is_featured=&sort=newest')
            ->assertOk()
            ->assertSee($active->sku)
            ->assertSee($inactive->sku);
    }

    public function test_unused_product_is_soft_deleted_with_variants(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();
        $variant = $product->productVariants()->create(['sku' => 'TSHIRT-001-M', 'name' => 'Size M', 'status' => true]);

        $this->actingAs($admin)->delete(route('admin.products.destroy', $product))->assertSessionHasNoErrors();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertSoftDeleted('product_variants', ['id' => $variant->id]);
    }

    public function test_product_used_by_order_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();
        $orderId = DB::table('orders')->insertGetId([
            'order_code' => 'ORD-001', 'customer_name' => 'Customer', 'customer_phone' => '0900000000',
            'shipping_address' => 'Hanoi', 'currency_code' => 'VND', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('order_items')->insert([
            'order_id' => $orderId, 'product_id' => $product->id, 'product_name' => 'Áo thun đen',
            'product_sku' => $product->sku, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->delete(route('admin.products.destroy', $product))->assertSessionHasErrors('product');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }

    private function payload(): array
    {
        return [
            'category_id' => $this->category->id, 'tax_class_id' => $this->taxClass->id, 'sku' => 'TSHIRT-NEW',
            'price' => 200000, 'sale_price' => 180000, 'cost_price' => 100000, 'status' => '1', 'is_featured' => '1',
            'translations' => [
                'vi' => ['name' => 'Áo thun nam màu đen', 'slug' => '', 'short_description' => 'Áo thun nam', 'description' => 'Mô tả đầy đủ'],
                'en' => ['name' => 'Black Men T-Shirt', 'slug' => '', 'short_description' => 'Black shirt'],
                'ja' => ['name' => '', 'slug' => ''],
            ],
            'variants' => [
                ['sku' => 'TSHIRT-NEW-M', 'name' => 'Size M', 'price' => '', 'sale_price' => '', 'status' => '1'],
            ],
        ];
    }

    private function createProduct(string $sku = 'TSHIRT-001', string $name = 'Áo thun đen', string $slug = 'ao-thun-den'): Product
    {
        $product = Product::query()->create([
            'category_id' => $this->category->id, 'tax_class_id' => $this->taxClass->id,
            'sku' => $sku, 'price' => 200000, 'status' => true, 'is_featured' => false,
        ]);
        $product->productTranslations()->create(['language_code' => 'vi', 'name' => $name, 'slug' => $slug]);

        return $product;
    }
}
