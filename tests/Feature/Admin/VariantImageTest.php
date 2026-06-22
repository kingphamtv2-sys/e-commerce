<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantImage;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VariantImageTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ProductVariant $variant;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed([LanguageSeeder::class, CurrencySeeder::class]);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create(['sort_order' => 0, 'status' => true]);
        $category->categoryTranslations()->create(['language_code' => 'vi', 'name' => 'Điện thoại', 'slug' => 'dien-thoai']);
        $this->product = Product::query()->create(['category_id' => $category->id, 'sku' => 'PHONE-001', 'price' => 10_000_000, 'status' => true]);
        $this->product->productTranslations()->create(['language_code' => 'vi', 'name' => 'Điện thoại', 'slug' => 'dien-thoai-moi']);
        $this->variant = $this->product->productVariants()->create(['sku' => 'PHONE-BLACK', 'name' => 'Black / 128GB', 'status' => true]);
    }

    public function test_admin_can_open_variant_image_manager_and_product_edit_links_to_it(): void
    {
        $this->actingAs($this->admin)->get(route('admin.product-variants.images.index', $this->variant))
            ->assertOk()->assertSee('Hình ảnh biến thể')->assertSee('Black / 128GB')->assertSee('Biến thể này chưa có hình ảnh.');
        $this->actingAs($this->admin)->get(route('admin.products.edit', $this->product))
            ->assertOk()->assertSee(route('admin.product-variants.images.index', $this->variant), false)->assertSee('Quản lý ảnh');
    }

    public function test_admin_can_upload_multiple_images_and_first_active_image_becomes_main(): void
    {
        $this->actingAs($this->admin)->post(route('admin.product-variants.images.store', $this->variant), [
            'images' => [$this->image('front.png'), $this->image('back.png')],
            'alt_text' => 'Điện thoại màu đen', 'sort_order' => 3, 'status' => 1, 'is_main' => 0,
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $images = VariantImage::query()->orderBy('sort_order')->get();
        $this->assertCount(2, $images);
        $this->assertTrue($images[0]->is_main);
        $this->assertFalse($images[1]->is_main);
        $this->assertSame([3, 4], $images->pluck('sort_order')->all());
        $this->assertStringStartsWith("variant-images/{$this->variant->id}/", $images[0]->image_path);
        Storage::disk('public')->assertExists($images[0]->image_path);
    }

    public function test_upload_rejects_non_image_oversized_file_and_inactive_main(): void
    {
        $this->actingAs($this->admin)->post(route('admin.product-variants.images.store', $this->variant), [
            'images' => [UploadedFile::fake()->create('danger.php', 1, 'application/x-php')], 'status' => 1, 'is_main' => 0,
        ])->assertSessionHasErrors('images.0');
        $this->actingAs($this->admin)->post(route('admin.product-variants.images.store', $this->variant), [
            'images' => [UploadedFile::fake()->create('huge.jpg', 6000, 'image/jpeg')], 'status' => 1, 'is_main' => 0,
        ])->assertSessionHasErrors('images.0');
        $this->actingAs($this->admin)->post(route('admin.product-variants.images.store', $this->variant), [
            'images' => [$this->image('inactive.png')], 'status' => 0, 'is_main' => 1,
        ])->assertSessionHasErrors('is_main');
    }

    public function test_set_main_only_changes_images_of_the_same_variant(): void
    {
        $first = $this->record('first.png', true);
        $second = $this->record('second.png');
        $otherVariant = $this->product->productVariants()->create(['sku' => 'PHONE-BLUE', 'name' => 'Blue', 'status' => true]);
        $otherMain = VariantImage::query()->create(['product_variant_id' => $otherVariant->id, 'image_path' => 'variant-images/other.png', 'is_main' => true, 'status' => true]);

        $this->actingAs($this->admin)->post(route('admin.variant-images.set-main', $second))->assertSessionHasNoErrors();

        $this->assertFalse($first->fresh()->is_main);
        $this->assertTrue($second->fresh()->is_main);
        $this->assertTrue($otherMain->fresh()->is_main);
        $this->assertSame(1, VariantImage::query()->where('product_variant_id', $this->variant->id)->where('is_main', true)->count());
    }

    public function test_admin_can_update_metadata_and_disabling_main_promotes_active_fallback(): void
    {
        $main = $this->record('main.png', true, 1);
        $fallback = $this->record('fallback.png', false, 2);

        $this->actingAs($this->admin)->put(route('admin.variant-images.update', $main), [
            'alt_text' => 'Ảnh đã cập nhật', 'sort_order' => 9, 'status' => 0, 'is_main' => 0,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('variant_images', ['id' => $main->id, 'alt_text' => 'Ảnh đã cập nhật', 'sort_order' => 9, 'status' => 0, 'is_main' => 0]);
        $this->assertTrue($fallback->fresh()->is_main);
        $this->actingAs($this->admin)->post(route('admin.variant-images.set-main', $main))->assertSessionHasErrors('variant_image');
    }

    public function test_deleting_main_removes_file_and_promotes_fallback_even_if_file_is_missing(): void
    {
        $main = $this->record('main.png', true, 1, true);
        $fallback = $this->record('fallback.png', false, 2, true);
        Storage::disk('public')->delete($main->image_path);

        $this->actingAs($this->admin)->delete(route('admin.variant-images.destroy', $main))->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('variant_images', ['id' => $main->id]);
        $this->assertTrue($fallback->fresh()->is_main);
        Storage::disk('public')->assertExists($fallback->image_path);
    }

    public function test_guest_and_customer_cannot_manage_variant_images(): void
    {
        $this->get(route('admin.product-variants.images.index', $this->variant))->assertRedirect(route('login'));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get(route('admin.product-variants.images.index', $this->variant))->assertForbidden();
    }

    public function test_product_detail_embeds_active_variant_gallery_and_falls_back_to_product_gallery(): void
    {
        ProductImage::query()->create(['product_id' => $this->product->id, 'image_path' => 'product-images/general.png', 'alt_text' => 'Ảnh chung', 'sort_order' => 0, 'status' => true, 'is_main' => true]);
        $active = $this->record('variant-active.png', true);
        $inactive = $this->record('variant-hidden.png', false, 2);
        $inactive->update(['status' => false]);

        $response = $this->get('/products/dien-thoai-moi');
        $response->assertOk()
            ->assertSee('general.png')
            ->assertSee('variant-active.png')
            ->assertDontSee('variant-hidden.png')
            ->assertSee('currentImages', false)
            ->assertViewHas('variantOptions', fn (array $options): bool => $options[0]['images'][0]['url'] === '/storage/'.$active->image_path);

        $active->delete();
        $fallbackResponse = $this->get('/products/dien-thoai-moi');
        $variantOptions = $fallbackResponse->viewData('variantOptions');
        $this->assertSame([], $variantOptions[0]['images']);
    }

    public function test_catalog_uses_active_variant_image_only_when_product_has_no_general_image(): void
    {
        $variantImage = $this->record('catalog-variant.png', true);
        $this->get('/products')->assertOk()->assertSee('/storage/'.$variantImage->image_path, false);

        $variantImage->update(['status' => false]);
        $this->get('/products')->assertOk()->assertDontSee('/storage/'.$variantImage->image_path, false)->assertSee('Chưa có ảnh');
    }

    public function test_async_upload_returns_variant_image_cards_without_redirect(): void
    {
        $response = $this->actingAs($this->admin)->withHeader('Accept', 'application/json')->post(route('admin.product-variants.images.store', $this->variant), [
            'images' => [$this->image('async.png')], 'alt_text' => 'Ảnh biến thể async',
            'sort_order' => 0, 'status' => 1, 'is_main' => 0,
        ]);

        $image = VariantImage::query()->firstOrFail();
        $response->assertOk()->assertJsonPath('success', true)->assertJsonPath('main_image_id', $image->id)->assertJsonPath('image_count', 1)->assertJsonPath('variant_id', $this->variant->id);
        $this->assertStringContainsString('data-variant-image-card="'.$image->id.'"', $response->json('html'));
    }

    private function image(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Zl1sAAAAASUVORK5CYII='));
    }

    private function record(string $name, bool $main = false, int $sortOrder = 0, bool $store = false): VariantImage
    {
        $path = "variant-images/{$this->variant->id}/{$name}";
        if ($store) {
            Storage::disk('public')->put($path, 'image');
        }

        return VariantImage::query()->create([
            'product_variant_id' => $this->variant->id, 'image_path' => $path,
            'alt_text' => $name, 'is_main' => $main, 'sort_order' => $sortOrder, 'status' => true,
        ]);
    }
}
