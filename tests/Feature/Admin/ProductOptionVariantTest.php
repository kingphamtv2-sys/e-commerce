<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ProductVariantService;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductOptionVariantTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, CurrencySeeder::class]);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create(['sort_order' => 0, 'status' => true]);
        $category->categoryTranslations()->create(['language_code' => 'vi', 'name' => 'Sản phẩm', 'slug' => 'san-pham']);
        $this->product = Product::query()->create(['category_id' => $category->id, 'sku' => 'DYNAMIC-001', 'price' => 1_000_000, 'sale_price' => 900_000, 'status' => true]);
        $this->product->productTranslations()->create(['language_code' => 'vi', 'name' => 'Sản phẩm động', 'slug' => 'san-pham-dong']);
    }

    public function test_admin_can_create_update_and_delete_dynamic_product_option(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.options.store', $this->product), [
            'name' => 'Color', 'display_name' => 'Màu sắc', 'sort_order' => 1, 'status' => '1',
        ])->assertSessionHasNoErrors();

        $option = ProductOption::query()->firstOrFail();
        $this->assertDatabaseHas('product_options', ['product_id' => $this->product->id, 'name' => 'Color', 'display_name' => 'Màu sắc', 'status' => 1]);

        $this->actingAs($this->admin)->put(route('admin.product-options.update', $option), [
            'name' => 'Finish', 'display_name' => 'Bề mặt', 'sort_order' => 2, 'status' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('product_options', ['id' => $option->id, 'name' => 'Finish', 'status' => 0]);

        $this->actingAs($this->admin)->delete(route('admin.product-options.destroy', $option))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('product_options', ['id' => $option->id]);
    }

    public function test_option_name_and_value_are_unique_in_their_parent_scope(): void
    {
        $color = $this->option('Color');
        $this->option('Size');
        $black = $this->value($color, 'Black');

        $this->actingAs($this->admin)->post(route('admin.products.options.store', $this->product), [
            'name' => 'Color', 'sort_order' => 0, 'status' => 1,
        ])->assertSessionHasErrors('name');
        $this->actingAs($this->admin)->post(route('admin.product-options.values.store', $color), [
            'value' => 'Black', 'sort_order' => 0, 'status' => 1,
        ])->assertSessionHasErrors('value');

        $this->actingAs($this->admin)->put(route('admin.product-option-values.update', $black), [
            'value' => 'Midnight', 'display_value' => 'Đen', 'color_code' => '#000000', 'sort_order' => 2, 'status' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('product_option_values', ['id' => $black->id, 'value' => 'Midnight', 'color_code' => '#000000']);
    }

    public function test_admin_can_create_two_option_combination_with_generated_name_and_inventory(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();

        $this->actingAs($this->admin)->post(route('admin.products.variants.store', $this->product), $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]))->assertSessionHasNoErrors();

        $variant = ProductVariant::query()->where('sku', 'DYNAMIC-BLACK-M')->firstOrFail();
        $this->assertSame('Black / M', $variant->name);
        $this->assertDatabaseHas('product_variant_option_values', ['product_variant_id' => $variant->id, 'product_option_id' => $color->id, 'product_option_value_id' => $black->id]);
        $this->assertDatabaseHas('product_variant_option_values', ['product_variant_id' => $variant->id, 'product_option_id' => $size->id, 'product_option_value_id' => $medium->id]);
        $this->assertDatabaseHas('inventory_stocks', ['product_id' => $this->product->id, 'product_variant_id' => $variant->id, 'quantity' => 0]);
        $this->assertDatabaseHas('inventory_logs', ['product_variant_id' => $variant->id, 'type' => 'initial']);
    }

    public function test_combinations_support_any_number_and_names_of_options(): void
    {
        $ram = $this->option('Memory');
        $ram16 = $this->value($ram, '16GB');
        $storage = $this->option('Capacity');
        $storage512 = $this->value($storage, '512GB');
        $finish = $this->option('Finish');
        $silver = $this->value($finish, 'Silver');

        $variant = app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload([
            $ram->id => $ram16->id, $storage->id => $storage512->id, $finish->id => $silver->id,
        ]), $this->admin);

        $this->assertSame('16GB / 512GB / Silver', $variant->name);
        $this->assertCount(3, $variant->optionValues);
    }

    public function test_variant_requires_one_active_value_for_every_active_option(): void
    {
        [$color, $black, $size] = $this->colorAndSize();

        $this->actingAs($this->admin)->post(route('admin.products.variants.store', $this->product), $this->variantPayload([
            $color->id => $black->id,
        ]))->assertSessionHasErrors("option_values.{$size->id}");

        $otherProduct = Product::query()->create(['category_id' => $this->product->category_id, 'sku' => 'OTHER', 'price' => 1, 'status' => true]);
        $otherOption = $this->option('Other', $otherProduct);
        $otherValue = $this->value($otherOption, 'Foreign');
        $this->actingAs($this->admin)->post(route('admin.products.variants.store', $this->product), $this->variantPayload([
            $color->id => $otherValue->id, $size->id => $otherValue->id,
        ]))->assertSessionHasErrors(["option_values.{$color->id}", "option_values.{$size->id}"]);
    }

    public function test_duplicate_combination_sku_conflicts_and_invalid_sale_price_are_rejected(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();
        $selection = [$color->id => $black->id, $size->id => $medium->id];
        app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload($selection), $this->admin);

        $duplicate = $this->variantPayload($selection);
        $duplicate['sku'] = 'DYNAMIC-DUPLICATE';
        $this->actingAs($this->admin)->post(route('admin.products.variants.store', $this->product), $duplicate)
            ->assertSessionHasErrors('variant');

        $conflict = $this->variantPayload($selection);
        $conflict['sku'] = $this->product->sku;
        $conflict['price'] = 100;
        $conflict['sale_price'] = 200;
        $this->actingAs($this->admin)->post(route('admin.products.variants.store', $this->product), $conflict)
            ->assertSessionHasErrors(['sku', 'sale_price']);
    }

    public function test_admin_can_update_combination_price_sku_status_and_values(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();
        $white = $this->value($color, 'White');
        $variant = app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]), $this->admin);

        $payload = $this->variantPayload([$color->id => $white->id, $size->id => $medium->id]);
        $payload['sku'] = 'DYNAMIC-WHITE-M';
        $payload['name'] = '';
        $payload['price'] = 1_200_000;
        $payload['sale_price'] = 1_100_000;
        $payload['status'] = '0';
        $this->actingAs($this->admin)->put(route('admin.product-variants.update', $variant), $payload)->assertSessionHasNoErrors();

        $variant->refresh();
        $this->assertSame('White / M', $variant->name);
        $this->assertFalse($variant->status);
        $this->assertSame('1200000.00', $variant->price);
        $this->assertTrue($variant->optionValues->contains('id', $white->id));
    }

    public function test_option_and_value_used_by_variant_cannot_be_deleted(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();
        $variant = app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]), $this->admin);

        $this->actingAs($this->admin)->delete(route('admin.product-options.destroy', $color))->assertSessionHasErrors('option');
        $this->actingAs($this->admin)->delete(route('admin.product-option-values.destroy', $black))->assertSessionHasErrors('option_value');
        $this->assertDatabaseHas('product_options', ['id' => $color->id]);
        $this->assertDatabaseHas('product_option_values', ['id' => $black->id]);
    }

    public function test_unused_variant_can_be_deleted_but_variant_with_stock_history_must_be_disabled(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();
        $service = app(ProductVariantService::class);
        $variant = $service->createVariant($this->product, $this->variantPayload([$color->id => $black->id, $size->id => $medium->id]), $this->admin);

        $this->actingAs($this->admin)->delete(route('admin.product-variants.destroy', $variant))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);

        $variant = $service->createVariant($this->product, $this->variantPayload([$color->id => $black->id, $size->id => $medium->id]), $this->admin);
        $stock = $variant->inventoryStock;
        app(InventoryService::class)->adjust($stock, ['adjustment_type' => 'increase', 'quantity' => 5, 'low_stock_threshold' => 2], $this->admin);
        $this->actingAs($this->admin)->delete(route('admin.product-variants.destroy', $variant))->assertSessionHasErrors('variant');
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'deleted_at' => null]);
    }

    public function test_product_edit_and_public_detail_render_dynamic_options_and_inventory_recognizes_variant(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();
        $variant = app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]), $this->admin);

        $this->actingAs($this->admin)->get(route('admin.products.edit', $this->product))
            ->assertOk()->assertSee('Tùy chọn sản phẩm')->assertSee('Tổ hợp biến thể')->assertSee('Color')->assertSee('Black')->assertSee('DYNAMIC-BLACK-M');

        $this->get('/products/san-pham-dong')
            ->assertOk()->assertSee('Color')->assertSee('Size')->assertSee('Black')->assertSee('M')
            ->assertSee('option_value_ids', false);

        $this->actingAs($this->admin)->get('/admin/inventory?product_type=variant')
            ->assertOk()->assertSee($variant->sku)->assertSee($variant->name);
    }

    public function test_updating_basic_product_data_without_variants_keeps_combinations(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();
        $variant = app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]), $this->admin);

        $this->actingAs($this->admin)->put(route('admin.products.update', $this->product), [
            'category_id' => $this->product->category_id, 'sku' => $this->product->sku, 'price' => 1_100_000,
            'status' => 1, 'is_featured' => 0,
            'translations' => ['vi' => ['name' => 'Sản phẩm cập nhật', 'slug' => 'san-pham-dong']],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('product_variant_option_values', ['product_variant_id' => $variant->id, 'product_option_value_id' => $black->id]);
    }

    public function test_guest_and_customer_cannot_manage_options_or_variants(): void
    {
        $payload = ['name' => 'Color', 'sort_order' => 0, 'status' => 1];
        $this->post(route('admin.products.options.store', $this->product), $payload)->assertRedirect(route('login'));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->post(route('admin.products.options.store', $this->product), $payload)->assertForbidden();
    }

    public function test_async_edit_endpoints_return_json_without_redirecting(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();
        $variant = app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]), $this->admin);

        $this->actingAs($this->admin)->putJson(route('admin.product-options.update', $color), [
            'name' => 'Colour', 'display_name' => 'Màu sắc', 'sort_order' => 1, 'status' => true,
        ])->assertOk()->assertJsonPath('option.name', 'Colour');

        $this->actingAs($this->admin)->putJson(route('admin.product-option-values.update', $black), [
            'value' => 'Midnight', 'display_value' => 'Đen', 'color_code' => '#000000', 'sort_order' => 1, 'status' => true,
        ])->assertOk()->assertJsonPath('value.value', 'Midnight');

        $payload = $this->variantPayload([$color->id => $black->id, $size->id => $medium->id]);
        $payload['price'] = 1_100_000;
        $this->actingAs($this->admin)->putJson(route('admin.product-variants.update', $variant), $payload)
            ->assertOk()->assertJsonPath('variant.id', $variant->id);
    }

    public function test_async_duplicate_validation_returns_json_errors(): void
    {
        $color = $this->option('Color');
        $size = $this->option('Size');
        $black = $this->value($color, 'Black');
        $medium = $this->value($size, 'M');
        $variant = app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]), $this->admin);

        $this->actingAs($this->admin)->postJson(route('admin.products.options.store', $this->product), [
            'name' => 'Color', 'sort_order' => 0, 'status' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('name');

        $duplicatePayload = $this->variantPayload([$color->id => $black->id, $size->id => $medium->id]);
        $duplicatePayload['sku'] = 'ANOTHER-SKU';
        $this->actingAs($this->admin)->postJson(route('admin.products.variants.store', $this->product), $duplicatePayload)
            ->assertUnprocessable()->assertJsonValidationErrors('variant');

        $this->assertSame(1, ProductVariant::query()->whereNull('deleted_at')->count());
        $this->assertNotNull($variant);
    }

    public function test_product_edit_renders_async_save_coordinator_and_states(): void
    {
        $color = $this->option('Color');
        $this->value($color, 'Black');

        $this->actingAs($this->admin)->get(route('admin.products.edit', $this->product))
            ->assertOk()
            ->assertSee('id="product-main-form"', false)
            ->assertSee('data-async-save', false)
            ->assertSee("setState(form, 'unsaved')", false)
            ->assertSee('dirtyEditors', false)
            ->assertSee("'Accept': 'application/json'", false);
    }

    public function test_async_option_value_and_variant_creation_return_rendered_ui(): void
    {
        $color = $this->option('Color');
        $size = $this->option('Size');
        $medium = $this->value($size, 'M');

        $valueResponse = $this->actingAs($this->admin)->postJson(route('admin.product-options.values.store', $color), [
            'value' => 'Black', 'display_value' => 'Đen', 'color_code' => '#000000', 'sort_order' => 0, 'status' => true,
        ])->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['html', 'value']);
        $black = ProductOptionValue::query()->where('value', 'Black')->firstOrFail();
        $this->assertStringContainsString('data-option-value-row="'.$black->id.'"', $valueResponse->json('html'));

        $variantResponse = $this->actingAs($this->admin)->postJson(route('admin.products.variants.store', $this->product), $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]))->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['html', 'variant', 'variant_image_panel_html', 'inventory_html']);
        $variant = ProductVariant::query()->where('sku', 'DYNAMIC-BLACK-M')->firstOrFail();
        $this->assertStringContainsString('data-variant-row="'.$variant->id.'"', $variantResponse->json('html'));
    }

    public function test_async_option_can_be_followed_by_value_and_variant_without_reload(): void
    {
        $optionResponse = $this->actingAs($this->admin)->postJson(route('admin.products.options.store', $this->product), [
            'name' => 'Storage', 'display_name' => 'Dung lượng', 'sort_order' => 0, 'status' => true,
        ])->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['html', 'variant_selector_html', 'option']);

        $option = ProductOption::query()->where('name', 'Storage')->firstOrFail();
        $this->assertStringContainsString('data-product-option="'.$option->id.'"', $optionResponse->json('html'));
        $this->assertStringContainsString('data-variant-selector="'.$option->id.'"', $optionResponse->json('variant_selector_html'));

        $valueResponse = $this->actingAs($this->admin)->postJson(route('admin.product-options.values.store', $option), [
            'value' => '256GB', 'sort_order' => 0, 'status' => true,
        ])->assertOk()->assertJsonPath('success', true);
        $value = ProductOptionValue::query()->where('value', '256GB')->firstOrFail();
        $this->assertSame($value->id, $valueResponse->json('value.id'));

        $this->actingAs($this->admin)->postJson(route('admin.products.variants.store', $this->product), $this->variantPayload([
            $option->id => $value->id,
        ]))->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('product_variants', ['product_id' => $this->product->id, 'sku' => 'DYNAMIC-BLACK-M']);
    }

    public function test_product_edit_contains_async_option_targets_and_smooth_overlay(): void
    {
        $this->actingAs($this->admin)->get(route('admin.products.edit', $this->product))
            ->assertOk()
            ->assertSee('data-append-target="#product-option-list"', false)
            ->assertSee('id="variant-option-selectors"', false)
            ->assertSee('id="variant-create-form"', false)
            ->assertSee('transition-opacity', false);

        $script = file_get_contents(resource_path('js/admin-async-forms.js'));
        $this->assertStringContainsString('OVERLAY_DELAY', $script);
        $this->assertStringContainsString('addVariantSelector', $script);
    }

    public function test_product_edit_creation_actions_are_rendered_in_modals(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();
        $variant = app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]), $this->admin);

        $this->actingAs($this->admin)->get(route('admin.products.edit', $this->product))
            ->assertOk()
            ->assertSee('add-product-option-title', false)
            ->assertSee('add-option-value-'.$color->id.'-title', false)
            ->assertSee('create-product-variant-title', false)
            ->assertSee('upload-product-images-title', false)
            ->assertSee('upload-variant-images-'.$variant->id.'-title', false)
            ->assertSee('adjust-inventory-'.$variant->inventoryStock->id.'-title', false)
            ->assertSee('data-modal-panel', false)
            ->assertSee('data-create-variant-trigger', false);
    }

    public function test_product_edit_uses_custom_ajax_delete_modal_for_options_and_variants(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();
        $variant = app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]), $this->admin);

        $response = $this->actingAs($this->admin)->get(route('admin.products.edit', $this->product));

        $response->assertOk()
            ->assertSee('data-admin-delete-modal', false)
            ->assertSee('data-async-delete', false)
            ->assertSee('data-delete-url="'.route('admin.product-options.destroy', $color).'"', false)
            ->assertSee('data-delete-url="'.route('admin.product-option-values.destroy', $black).'"', false)
            ->assertSee('data-delete-url="'.route('admin.product-variants.destroy', $variant).'"', false)
            ->assertDontSee('form="option-delete-'.$color->id.'"', false)
            ->assertDontSee('form="value-delete-'.$black->id.'"', false)
            ->assertDontSee('form="variant-delete-'.$variant->id.'"', false)
            ->assertDontSee('data-save-status class="text-xs font-bold text-emerald-600"', false)
            ->assertDontSee('data-save-status class="text-[11px] font-bold text-emerald-600"', false)
            ->assertDontSee('data-save-status class="pb-2 text-[11px] font-bold text-emerald-600"', false);
    }

    public function test_async_option_delete_returns_json_and_removes_unused_option(): void
    {
        $option = $this->option('Material');
        $this->value($option, 'Cotton');

        $this->actingAs($this->admin)->deleteJson(route('admin.product-options.destroy', $option))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('option_id', $option->id);

        $this->assertDatabaseMissing('product_options', ['id' => $option->id]);
        $this->assertDatabaseMissing('product_option_values', ['product_option_id' => $option->id]);
    }

    public function test_async_option_value_delete_returns_json_and_removes_unused_value(): void
    {
        $option = $this->option('Material');
        $value = $this->value($option, 'Cotton');

        $this->actingAs($this->admin)->deleteJson(route('admin.product-option-values.destroy', $value))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('option_value_id', $value->id)
            ->assertJsonPath('option_id', $option->id);

        $this->assertDatabaseMissing('product_option_values', ['id' => $value->id]);
    }

    public function test_async_delete_failures_return_json_errors_without_deleting_related_data(): void
    {
        [$color, $black, $size, $medium] = $this->colorAndSize();
        $variant = app(ProductVariantService::class)->createVariant($this->product, $this->variantPayload([
            $color->id => $black->id, $size->id => $medium->id,
        ]), $this->admin);

        $this->actingAs($this->admin)->deleteJson(route('admin.product-options.destroy', $color))
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('option');

        $variant->variantImages()->create(['image_path' => 'products/variants/demo.jpg', 'is_main' => true, 'status' => true]);

        $this->actingAs($this->admin)->deleteJson(route('admin.product-variants.destroy', $variant))
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('variant');

        $this->assertDatabaseHas('product_options', ['id' => $color->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'deleted_at' => null]);
    }

    private function option(string $name, ?Product $product = null): ProductOption
    {
        return ($product ?? $this->product)->productOptions()->create(['name' => $name, 'sort_order' => ProductOption::query()->count(), 'status' => true]);
    }

    private function value(ProductOption $option, string $value): ProductOptionValue
    {
        return $option->values()->create(['value' => $value, 'sort_order' => $option->values()->count(), 'status' => true]);
    }

    private function colorAndSize(): array
    {
        $color = $this->option('Color');
        $black = $this->value($color, 'Black');
        $size = $this->option('Size');
        $medium = $this->value($size, 'M');

        return [$color, $black, $size, $medium];
    }

    private function variantPayload(array $selection): array
    {
        return [
            'option_values' => $selection,
            'sku' => 'DYNAMIC-BLACK-M',
            'name' => '',
            'price' => '',
            'sale_price' => '',
            'status' => '1',
        ];
    }
}
