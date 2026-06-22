<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Services\ProductImageService;
use App\Services\ProductService;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed([LanguageSeeder::class, CurrencySeeder::class]);
        $category = Category::query()->create(['sort_order' => 0, 'status' => true]);
        $category->categoryTranslations()->create(['language_code' => 'vi', 'name' => 'Áo nam', 'slug' => 'ao-nam']);
        $this->product = Product::query()->create([
            'category_id' => $category->id, 'sku' => 'TSHIRT-001', 'price' => 200000, 'status' => true,
        ]);
        $this->product->productTranslations()->create(['language_code' => 'vi', 'name' => 'Áo thun', 'slug' => 'ao-thun']);
    }

    public function test_admin_product_edit_displays_image_management_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.products.edit', $this->product))
            ->assertOk()->assertSee('Hình ảnh sản phẩm')->assertSee('Upload hình ảnh');
    }

    public function test_guest_and_customer_cannot_manage_product_images(): void
    {
        $this->post(route('admin.products.images.store', $this->product))->assertRedirect(route('login'));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->post(route('admin.products.images.store', $this->product))->assertForbidden();
    }

    public function test_admin_can_upload_multiple_images_and_first_image_becomes_main(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Cache::put(ProductService::CACHE_ALL, ['stale']);

        $this->actingAs($admin)->post(route('admin.products.images.store', $this->product), [
            'images' => [
                UploadedFile::fake()->createWithContent('front.png', $this->pngBytes()),
                UploadedFile::fake()->createWithContent('back.png', $this->pngBytes()),
            ],
            'alt_text' => 'Áo thun đen', 'sort_order' => 5, 'status' => '1', 'is_main' => '0',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $images = ProductImage::query()->orderBy('sort_order')->get();
        $this->assertCount(2, $images);
        $this->assertTrue($images[0]->is_main);
        $this->assertFalse($images[1]->is_main);
        $this->assertSame([5, 6], $images->pluck('sort_order')->all());
        Storage::disk('public')->assertExists($images[0]->image_path);
        Storage::disk('public')->assertExists($images[1]->image_path);
        $this->assertStringNotContainsString('front.jpg', $images[0]->image_path);
        $this->assertTrue(Cache::missing(ProductService::CACHE_ALL));
    }

    public function test_upload_rejects_non_image_and_oversized_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.products.images.store', $this->product), [
            'images' => [UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;')],
            'status' => '1',
        ])->assertSessionHasErrors('images.0');

        $this->actingAs($admin)->post(route('admin.products.images.store', $this->product), [
            'images' => [UploadedFile::fake()->createWithContent('large.png', $this->pngBytes())->size(6000)],
            'status' => '1',
        ])->assertSessionHasErrors('images.0');
        $this->assertSame(0, ProductImage::query()->count());
    }

    public function test_admin_can_set_an_active_image_as_the_only_main_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $first = $this->image('first.jpg', true, true, 1);
        $second = $this->image('second.jpg', false, true, 2);

        $this->actingAs($admin)->put(route('admin.product-images.set-main', $second))
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertFalse($first->fresh()->is_main);
        $this->assertTrue($second->fresh()->is_main);
        $this->assertSame(1, ProductImage::query()->where('product_id', $this->product->id)->where('is_main', true)->count());
    }

    public function test_inactive_image_cannot_be_set_as_main(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $image = $this->image('inactive.jpg', false, false, 1);

        $this->actingAs($admin)->put(route('admin.product-images.set-main', $image))->assertSessionHasErrors('product_image');
        $this->assertFalse($image->fresh()->is_main);

        $this->actingAs($admin)->put(route('admin.product-images.update', $image), [
            'alt_text' => '', 'sort_order' => 1, 'status' => '0', 'is_main' => '1',
        ])->assertSessionHasErrors('is_main');
    }

    public function test_admin_can_update_image_information_and_disabling_main_promotes_fallback(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $main = $this->image('main.jpg', true, true, 1);
        $fallback = $this->image('fallback.jpg', false, true, 2);

        $this->actingAs($admin)->put(route('admin.product-images.update', $main), [
            'alt_text' => 'Updated alt', 'sort_order' => 9, 'status' => '0', 'is_main' => '0',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseHas('product_images', ['id' => $main->id, 'alt_text' => 'Updated alt', 'sort_order' => 9, 'status' => 0, 'is_main' => 0]);
        $this->assertTrue($fallback->fresh()->is_main);
    }

    public function test_deleting_main_image_removes_file_and_promotes_active_fallback(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $main = $this->image('main.jpg', true, true, 1);
        $fallback = $this->image('fallback.jpg', false, true, 2);
        Storage::disk('public')->put($main->image_path, 'image');

        $this->actingAs($admin)->delete(route('admin.product-images.destroy', $main))
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseMissing('product_images', ['id' => $main->id]);
        Storage::disk('public')->assertMissing($main->image_path);
        $this->assertTrue($fallback->fresh()->is_main);
    }

    public function test_deleting_image_record_succeeds_when_physical_file_is_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $image = $this->image('missing.jpg', false, true, 1);

        $this->actingAs($admin)->delete(route('admin.product-images.destroy', $image))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }

    public function test_edit_page_handles_missing_image_file_without_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->image('missing.jpg', true, true, 1);

        $this->actingAs($admin)->get(route('admin.products.edit', $this->product))
            ->assertOk()->assertSee('Không tìm thấy tệp ảnh');
    }

    public function test_image_url_is_relative_and_does_not_depend_on_app_url_host(): void
    {
        $image = $this->image('preview.jpg', true, true, 1);

        $this->assertSame(
            "/storage/product-images/{$this->product->id}/preview.jpg",
            app(ProductImageService::class)->url($image),
        );
    }

    public function test_async_upload_returns_image_cards_without_redirect(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->withHeader('Accept', 'application/json')->post(route('admin.products.images.store', $this->product), [
            'images' => [UploadedFile::fake()->createWithContent('async.png', $this->pngBytes())],
            'alt_text' => 'Ảnh async', 'status' => '1', 'is_main' => '0',
        ]);

        $image = ProductImage::query()->firstOrFail();
        $response->assertOk()->assertJsonPath('success', true)->assertJsonPath('main_image_id', $image->id)->assertJsonPath('image_count', 1);
        $this->assertStringContainsString('data-product-image-card="'.$image->id.'"', $response->json('html'));
    }

    private function image(string $path, bool $isMain, bool $status, int $sortOrder): ProductImage
    {
        return $this->product->productImages()->create([
            'image_path' => "product-images/{$this->product->id}/{$path}",
            'alt_text' => null,
            'sort_order' => $sortOrder,
            'status' => $status,
            'is_main' => $isMain,
        ]);
    }

    private function pngBytes(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);
    }
}
