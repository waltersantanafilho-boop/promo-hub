<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\Enums\OfferAvailability;
use App\Domain\Catalog\Enums\OfferStatus;
use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductGroup;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use App\Models\User;
use App\Services\Catalog\OfferService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_without_manage_products_cannot_access_products(): void
    {
        $user = $this->userWithOnlyAdminAccess();

        $this->actingAs($user)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.products.store'), [])->assertForbidden();
    }

    public function test_admin_can_access_all_product_pages(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create(['name' => 'iPhone 16 128GB Preto']);

        $this->actingAs($admin)->get(route('admin.products.index'))->assertOk()->assertSeeText('Produtos');
        $this->actingAs($admin)->get(route('admin.products.create'))->assertOk()->assertSeeText('Novo produto');
        $this->actingAs($admin)->get(route('admin.products.show', $product))->assertOk()->assertSeeText($product->name);
        $this->actingAs($admin)->get(route('admin.products.edit', $product))->assertOk()->assertSeeText('Editar produto');
    }

    public function test_valid_data_creates_product_with_json_attributes_and_generated_slug(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $group = ProductGroup::factory()->for($category)->for($brand)->create();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'product_group_id' => $group->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'iPhone 16 128GB Preto',
            'slug' => '',
            'description' => '',
            'gtin' => '7891234567890',
            'attributes' => '{"storage":"128GB","color":"Preto"}',
            'canonical_image_url' => 'https://example.test/iphone.jpg',
            'status' => 'active',
            'merged_into_id' => '',
        ]);

        $product = Product::query()->firstOrFail();
        $response->assertRedirectToRoute('admin.products.show', $product)->assertSessionHas('success');
        $this->assertSame('iphone-16-128gb-preto', $product->slug);
        $this->assertSame(['storage' => '128GB', 'color' => 'Preto'], $product->attributes);
        $this->assertNull($product->description);
        $this->assertSame(ProductStatus::Active, $product->status);
    }

    public function test_invalid_json_attributes_are_rejected(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('admin.products.store'), [
            ...$this->validProductData(),
            'attributes' => '{invalid}',
        ])->assertSessionHasErrors(['attributes' => 'Informe um JSON válido nos atributos.']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_can_be_updated_and_slug_must_remain_unique(): void
    {
        $admin = $this->userWithRole('admin');
        Product::factory()->create(['slug' => 'reserved-slug']);
        $product = Product::factory()->create(['slug' => 'old-slug']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            ...$this->validProductData($category, $brand),
            'name' => 'Produto atualizado',
            'slug' => 'reserved-slug',
        ])->assertSessionHasErrors(['slug']);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            ...$this->validProductData($category, $brand),
            'name' => 'Produto atualizado',
            'slug' => 'produto-atualizado',
        ])->assertRedirectToRoute('admin.products.show', $product);

        $this->assertSame('Produto atualizado', $product->refresh()->name);
        $this->assertSame('produto-atualizado', $product->slug);
    }

    public function test_product_cannot_merge_into_itself(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            ...$this->validProductData($product->category, $product->brand),
            'slug' => $product->slug,
            'status' => 'merged',
            'merged_into_id' => $product->id,
        ])->assertSessionHasErrors(['merged_into_id' => 'Um produto não pode ser mesclado nele mesmo.']);

        $this->assertSame(ProductStatus::Active, $product->refresh()->status);
        $this->assertNull($product->merged_into_id);
    }

    public function test_merged_target_is_only_kept_for_merged_status(): void
    {
        $admin = $this->userWithRole('admin');
        $target = Product::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($admin)->patch(route('admin.products.status', $product), [
            'status' => 'merged',
            'merged_into_id' => $target->id,
        ])->assertRedirectToRoute('admin.products.show', $product);

        $this->assertSame(ProductStatus::Merged, $product->refresh()->status);
        $this->assertSame($target->id, $product->merged_into_id);

        $this->actingAs($admin)->patch(route('admin.products.status', $product), [
            'status' => 'archived',
            'merged_into_id' => $target->id,
        ])->assertRedirectToRoute('admin.products.show', $product);

        $this->assertSame(ProductStatus::Archived, $product->refresh()->status);
        $this->assertNull($product->merged_into_id);
    }

    public function test_product_filters_are_applied(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $group = ProductGroup::factory()->for($category)->for($brand)->create();
        Product::factory()->for($category)->for($brand)->for($group)->create(['name' => 'Produto alvo', 'status' => ProductStatus::Archived]);
        Product::factory()->create(['name' => 'Outro produto', 'status' => ProductStatus::Active]);

        $response = $this->actingAs($admin)->get(route('admin.products.index', [
            'q' => 'alvo',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'product_group_id' => $group->id,
            'status' => 'archived',
        ]));

        $response->assertSeeText('Produto alvo')->assertDontSeeText('Outro produto')
            ->assertViewHas('products', fn ($products): bool => $products->total() === 1);
    }

    public function test_product_page_displays_its_offers(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();
        $store = Store::factory()->create(['name' => 'Amazon Brasil']);
        $source = StoreSource::factory()->for($store)->create();
        $offer = app(OfferService::class)->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'store_source_id' => $source->id,
            'external_id' => 'AMZ-123',
            'url' => 'https://example.test/offer',
            'price' => '4599.9000',
            'currency' => 'BRL',
            'availability' => OfferAvailability::InStock,
            'status' => OfferStatus::Active,
        ]);

        $this->actingAs($admin)->get(route('admin.products.show', $product))
            ->assertSeeText(['Ofertas', 'Amazon Brasil', 'Em estoque'])
            ->assertSee('href="'.route('admin.offers.show', $offer).'"', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function validProductData(?Category $category = null, ?Brand $brand = null): array
    {
        $category ??= Category::factory()->create();
        $brand ??= Brand::factory()->create();

        return [
            'product_group_id' => '',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Produto válido',
            'slug' => 'produto-valido',
            'description' => '',
            'gtin' => '',
            'attributes' => '',
            'canonical_image_url' => '',
            'status' => 'active',
            'merged_into_id' => '',
        ];
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        return User::factory()->create()->assignRole($role);
    }

    private function userWithOnlyAdminAccess(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        return User::factory()->create()->givePermissionTo('access admin');
    }
}
