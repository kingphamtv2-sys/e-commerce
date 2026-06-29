<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\InventoryService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Product $simpleProduct;

    private Product $variantProduct;

    private ProductVariant $mediumVariant;

    private ProductVariant $largeVariant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
        $this->category = Category::query()->create(['sort_order' => 0, 'status' => true]);
        $this->category->categoryTranslations()->create(['language_code' => 'vi', 'name' => 'Áo nam', 'slug' => 'ao-nam']);
        $this->simpleProduct = $this->product('SIMPLE-001', 'Áo sơ mi', 'ao-so-mi');
        $this->variantProduct = $this->product('TSHIRT-001', 'Áo thun', 'ao-thun');
        $this->mediumVariant = $this->variantProduct->productVariants()->create(['sku' => 'TSHIRT-M', 'name' => 'Size M', 'status' => true]);
        $this->largeVariant = $this->variantProduct->productVariants()->create(['sku' => 'TSHIRT-L', 'name' => 'Size L', 'status' => true]);
    }

    public function test_guest_and_customer_cannot_access_inventory(): void
    {
        $this->get('/admin/inventory')->assertRedirect(route('login'));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin/inventory')->assertForbidden();
    }

    public function test_admin_inventory_list_auto_creates_product_and_variant_stocks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/inventory')
            ->assertOk()->assertSee('SIMPLE-001')->assertSee('TSHIRT-M')->assertSee('TSHIRT-L');

        $this->assertDatabaseHas('inventory_stocks', ['product_id' => $this->simpleProduct->id, 'product_variant_id' => null, 'quantity' => 0, 'reserved_quantity' => 0]);
        $this->assertDatabaseHas('inventory_stocks', ['product_id' => $this->variantProduct->id, 'product_variant_id' => $this->mediumVariant->id]);
        $this->assertDatabaseMissing('inventory_stocks', ['product_id' => $this->variantProduct->id, 'product_variant_id' => null]);
        $this->assertSame(3, InventoryStock::query()->count());
        $this->assertSame(3, InventoryLog::query()->where('type', 'initial')->count());
    }

    public function test_stock_auto_creation_is_idempotent(): void
    {
        $service = app(InventoryService::class);
        $admin = User::factory()->create(['role' => 'admin']);

        $service->syncStockRecords($admin);
        $service->syncStockRecords($admin);

        $this->assertSame(3, InventoryStock::query()->count());
        $this->assertSame(3, InventoryLog::query()->where('type', 'initial')->count());
    }

    public function test_admin_can_increase_inventory_and_log_the_change(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $stock = $this->stock($this->simpleProduct, null, 10);

        $this->actingAs($admin)->post(route('admin.inventory.update', $stock), $this->payload('increase', 5, 3))
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.inventory.show', $stock));

        $this->assertSame(15, $stock->fresh()->quantity);
        $this->assertSame(3, $stock->fresh()->low_stock_threshold);
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_stock_id' => $stock->id, 'type' => 'increase', 'quantity_before' => 10,
            'quantity_change' => 5, 'quantity_after' => 15, 'reason' => 'Nhập hàng', 'created_by' => $admin->id,
        ]);
    }

    public function test_admin_can_decrease_inventory_and_log_negative_change(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $stock = $this->stock($this->simpleProduct, null, 10);

        $this->actingAs($admin)->post(route('admin.inventory.update', $stock), $this->payload('decrease', 4))
            ->assertSessionHasNoErrors();

        $this->assertSame(6, $stock->fresh()->quantity);
        $this->assertDatabaseHas('inventory_logs', ['inventory_stock_id' => $stock->id, 'type' => 'decrease', 'quantity_change' => -4, 'quantity_after' => 6]);
    }

    public function test_admin_can_set_inventory_quantity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $stock = $this->stock($this->simpleProduct, null, 10);

        $this->actingAs($admin)->post(route('admin.inventory.update', $stock), $this->payload('set', 25))
            ->assertSessionHasNoErrors();

        $this->assertSame(25, $stock->fresh()->quantity);
        $this->assertDatabaseHas('inventory_logs', ['inventory_stock_id' => $stock->id, 'type' => 'set', 'quantity_before' => 10, 'quantity_change' => 15, 'quantity_after' => 25]);
    }

    public function test_decrease_and_set_cannot_go_below_reserved_quantity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $stock = $this->stock($this->simpleProduct, null, 10, 7);

        $this->actingAs($admin)->from(route('admin.inventory.adjust', $stock))
            ->post(route('admin.inventory.update', $stock), $this->payload('decrease', 4))
            ->assertRedirect(route('admin.inventory.adjust', $stock))->assertSessionHasErrors('quantity');
        $this->actingAs($admin)->post(route('admin.inventory.update', $stock), $this->payload('set', 6))
            ->assertSessionHasErrors('quantity');

        $this->assertSame(10, $stock->fresh()->quantity);
        $this->assertSame(0, $stock->inventoryLogs()->count());
    }

    public function test_adjustment_validation_rejects_negative_quantity_invalid_type_and_threshold(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $stock = $this->stock($this->simpleProduct, null, 10);

        $this->actingAs($admin)->post(route('admin.inventory.update', $stock), [
            'adjustment_type' => 'invalid', 'quantity' => -1, 'low_stock_threshold' => -1,
        ])->assertSessionHasErrors(['adjustment_type', 'quantity', 'low_stock_threshold']);
    }

    public function test_stock_status_uses_available_quantity_and_threshold(): void
    {
        $out = $this->stock($this->simpleProduct, null, 5, 5, 2);
        $low = $this->stock($this->variantProduct, $this->mediumVariant, 6, 2, 5);
        $in = $this->stock($this->variantProduct, $this->largeVariant, 20, 2, 5);

        $this->assertSame(0, $out->availableQuantity());
        $this->assertSame('out_of_stock', $out->stockStatus());
        $this->assertSame('low_stock', $low->stockStatus());
        $this->assertSame('in_stock', $in->stockStatus());
    }

    public function test_inventory_filters_by_sku_category_status_and_product_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        app(InventoryService::class)->syncStockRecords($admin);
        InventoryStock::query()->where('product_variant_id', $this->mediumVariant->id)->update(['quantity' => 3, 'low_stock_threshold' => 5]);
        InventoryStock::query()->where('product_variant_id', $this->largeVariant->id)->update(['quantity' => 20, 'low_stock_threshold' => 5]);

        $this->actingAs($admin)->get('/admin/inventory?keyword=TSHIRT-M&category_id='.$this->category->id.'&stock_status=low_stock&product_type=variant')
            ->assertOk()->assertSee('TSHIRT-M')->assertDontSee('TSHIRT-L')->assertDontSee('SIMPLE-001');
    }

    public function test_admin_can_view_inventory_detail_and_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $stock = $this->stock($this->simpleProduct, null, 10);
        app(InventoryService::class)->adjust($stock, $this->payload('increase', 2), $admin);

        $this->actingAs($admin)->get(route('admin.inventory.show', $stock))->assertOk()->assertSee('Nhập hàng')->assertSee('+2');
        $this->actingAs($admin)->get(route('admin.inventory.logs', $stock))->assertOk()->assertSee('Nhập hàng')->assertSee($admin->name);
    }

    public function test_soft_deleted_product_is_ignored_without_breaking_inventory_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $stock = $this->stock($this->simpleProduct, null, 10);
        $this->simpleProduct->delete();

        $this->actingAs($admin)->get('/admin/inventory')->assertOk()->assertDontSee('SIMPLE-001');
        $this->actingAs($admin)->get(route('admin.inventory.show', $stock))->assertNotFound();
    }

    public function test_async_inventory_adjustment_returns_updated_row_and_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $stock = $this->stock($this->simpleProduct, null, 10);

        $response = $this->actingAs($admin)->postJson(route('admin.inventory.update', $stock), $this->payload('increase', 5, 3))
            ->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['html', 'log_html']);

        $this->assertSame(15, $stock->fresh()->quantity);
        $this->assertStringContainsString('id="inventory-row-'.$stock->id.'"', $response->json('html'));
        $this->assertStringContainsString('10 → 15', $response->json('log_html'));
    }

    private function payload(string $type, int $quantity, int $threshold = 5): array
    {
        return [
            'adjustment_type' => $type,
            'quantity' => $quantity,
            'low_stock_threshold' => $threshold,
            'reason' => 'Nhập hàng',
            'note' => 'Kiểm kê thủ công',
        ];
    }

    private function stock(Product $product, ?ProductVariant $variant, int $quantity, int $reserved = 0, int $threshold = 5): InventoryStock
    {
        return InventoryStock::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $quantity,
            'reserved_quantity' => $reserved,
            'low_stock_threshold' => $threshold,
        ]);
    }

    private function product(string $sku, string $name, string $slug): Product
    {
        $product = Product::query()->create([
            'category_id' => $this->category->id,
            'sku' => $sku,
            'price' => 100000,
            'status' => true,
        ]);
        $product->productTranslations()->create(['language_code' => 'vi', 'name' => $name, 'slug' => $slug]);

        return $product;
    }
}
