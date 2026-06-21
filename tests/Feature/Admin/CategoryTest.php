<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CategoryService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    public function test_admin_can_view_category_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->createCategory('Thời trang', 'thoi-trang');

        $this->actingAs($admin)->get('/admin/categories')->assertOk()->assertSee('Thời trang');
        $this->actingAs($admin)->get('/admin/categories/create')->assertOk()->assertSee('Tiếng Việt');
        $this->actingAs($admin)->get(route('admin.categories.edit', $category))->assertOk()->assertSee('thoi-trang');
    }

    public function test_guest_and_customer_cannot_access_categories(): void
    {
        $this->get('/admin/categories')->assertRedirect(route('login'));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin/categories')->assertForbidden();
    }

    public function test_admin_can_create_category_with_multiple_translations_and_generated_slugs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Cache::put(CategoryService::CACHE_ALL, ['stale']);

        $this->actingAs($admin)->post('/admin/categories', $this->payload())
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->firstOrFail();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'image' => 'categories/fashion.jpg', 'sort_order' => 2, 'status' => 1]);
        $this->assertDatabaseHas('category_translations', ['category_id' => $category->id, 'language_code' => 'vi', 'name' => 'Áo nam', 'slug' => 'ao-nam']);
        $this->assertDatabaseHas('category_translations', ['category_id' => $category->id, 'language_code' => 'en', 'name' => "Men's Shirts", 'slug' => 'mens-shirts']);
        $this->assertDatabaseMissing('category_translations', ['category_id' => $category->id, 'language_code' => 'ja']);
        $this->assertTrue(Cache::missing(CategoryService::CACHE_ALL));
    }

    public function test_default_language_name_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/categories', $this->payload([
            'translations' => ['vi' => ['name' => '', 'slug' => ''], 'en' => ['name' => 'Shirts']],
        ]))->assertSessionHasErrors('translations.vi.name');
    }

    public function test_slug_must_be_unique_within_same_language_but_may_repeat_in_another_language(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createCategory('Áo nam', 'ao-nam');

        $this->actingAs($admin)->post('/admin/categories', $this->payload([
            'translations' => [
                'vi' => ['name' => 'Áo khác', 'slug' => 'ao-nam'],
                'en' => ['name' => 'Other Shirt', 'slug' => 'ao-nam'],
            ],
        ]))->assertSessionHasErrors('translations.vi.slug');

        $this->actingAs($admin)->post('/admin/categories', $this->payload([
            'translations' => [
                'vi' => ['name' => 'Quần nam', 'slug' => 'quan-nam'],
                'en' => ['name' => 'Other Shirt', 'slug' => 'ao-nam'],
            ],
        ]))->assertSessionHasNoErrors();
    }

    public function test_admin_can_update_category_and_remove_optional_translation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->createCategory('Áo nam', 'ao-nam', "Men's Shirts", 'mens-shirts');

        $this->actingAs($admin)->put(route('admin.categories.update', $category), $this->payload([
            'status' => '0',
            'translations' => [
                'vi' => ['name' => 'Áo sơ mi nam', 'slug' => 'ao-so-mi-nam'],
                'en' => ['name' => '', 'slug' => '', 'description' => '', 'meta_title' => '', 'meta_description' => ''],
            ],
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'status' => 0]);
        $this->assertDatabaseHas('category_translations', ['category_id' => $category->id, 'language_code' => 'vi', 'slug' => 'ao-so-mi-nam']);
        $this->assertDatabaseMissing('category_translations', ['category_id' => $category->id, 'language_code' => 'en']);
    }

    public function test_category_cannot_use_itself_or_a_descendant_as_parent(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $parent = $this->createCategory('Thời trang', 'thoi-trang');
        $child = $this->createCategory('Áo nam', 'ao-nam', parentId: $parent->id);

        $this->actingAs($admin)->put(route('admin.categories.update', $parent), $this->payload([
            'parent_id' => $parent->id,
        ]))->assertSessionHasErrors('parent_id');

        $this->actingAs($admin)->put(route('admin.categories.update', $parent), $this->payload([
            'parent_id' => $child->id,
        ]))->assertSessionHasErrors('parent_id');
    }

    public function test_category_with_children_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $parent = $this->createCategory('Thời trang', 'thoi-trang');
        $this->createCategory('Áo nam', 'ao-nam', parentId: $parent->id);

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $parent))->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $parent->id, 'deleted_at' => null]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->createCategory('Thời trang', 'thoi-trang');
        Product::query()->create(['category_id' => $category->id, 'sku' => 'SKU-001', 'price' => 100000, 'status' => true]);

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_unused_category_can_be_deleted_with_its_translations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->createCategory('Thời trang', 'thoi-trang');

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))->assertSessionHasNoErrors();

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('category_translations', ['category_id' => $category->id]);
    }

    public function test_translation_falls_back_to_default_then_first_available(): void
    {
        $category = $this->createCategory('Áo nam', 'ao-nam', "Men's Shirts", 'mens-shirts');
        $service = app(CategoryService::class);

        $this->assertSame("Men's Shirts", $service->translation($category, 'en')?->name);
        $this->assertSame('Áo nam', $service->translation($category, 'ja')?->name);

        $category->categoryTranslations()->where('language_code', 'vi')->delete();
        $category->unsetRelation('categoryTranslations');
        $this->assertSame("Men's Shirts", $service->translation($category, 'ja')?->name);
    }

    public function test_category_list_supports_keyword_status_parent_filters_and_pagination(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $parent = $this->createCategory('Thời trang', 'thoi-trang');
        $match = $this->createCategory('Áo nam', 'ao-nam', parentId: $parent->id);
        $inactive = $this->createCategory('Quần nam', 'quan-nam', parentId: $parent->id);
        $inactive->update(['status' => false]);
        app(CategoryService::class)->clearCache();

        $this->actingAs($admin)->get('/admin/categories?keyword=Áo&status=1&parent_id='.$parent->id)
            ->assertOk()->assertSee('Áo nam')->assertDontSee('quan-nam');
        $this->assertSame($match->id, Category::query()->where('status', true)->where('parent_id', $parent->id)->latest('id')->value('id'));
    }

    public function test_category_all_filters_do_not_apply_null_filter_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $active = $this->createCategory('Danh mục hoạt động', 'danh-muc-hoat-dong');
        $inactive = $this->createCategory('Danh mục tạm dừng', 'danh-muc-tam-dung');
        $inactive->update(['status' => false]);
        app(CategoryService::class)->clearCache();

        $this->actingAs($admin)->get('/admin/categories?keyword=&status=&parent_id=')
            ->assertOk()
            ->assertSee($active->categoryTranslations()->firstOrFail()->name)
            ->assertSee($inactive->categoryTranslations()->firstOrFail()->name);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'parent_id' => '', 'image' => 'categories/fashion.jpg', 'sort_order' => 2, 'status' => '1',
            'translations' => [
                'vi' => ['name' => 'Áo nam', 'slug' => '', 'description' => 'Danh mục áo nam', 'meta_title' => 'Áo nam'],
                'en' => ['name' => "Men's Shirts", 'slug' => '', 'description' => 'Shirts for men'],
                'ja' => ['name' => '', 'slug' => ''],
            ],
        ], $overrides);
    }

    private function createCategory(
        string $viName,
        string $viSlug,
        ?string $enName = null,
        ?string $enSlug = null,
        ?int $parentId = null,
    ): Category {
        $category = Category::query()->create(['parent_id' => $parentId, 'sort_order' => 0, 'status' => true]);
        $category->categoryTranslations()->create(['language_code' => 'vi', 'name' => $viName, 'slug' => $viSlug]);

        if ($enName) {
            $category->categoryTranslations()->create(['language_code' => 'en', 'name' => $enName, 'slug' => $enSlug]);
        }

        return $category;
    }
}
