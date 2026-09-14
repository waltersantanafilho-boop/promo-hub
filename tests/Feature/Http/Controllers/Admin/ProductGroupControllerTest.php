<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductGroup;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductGroupControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_without_manage_products_cannot_access_product_groups(): void
    {
        $user = $this->userWithOnlyAdminAccess();

        $this->actingAs($user)->get(route('admin.product-groups.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.product-groups.store'), [])->assertForbidden();
    }

    public function test_admin_and_editor_can_access_product_group_pages(): void
    {
        $group = ProductGroup::factory()->create(['name' => 'iPhone 16']);

        foreach (['admin', 'editor'] as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user)->get(route('admin.product-groups.index'))->assertOk()->assertSeeText('Grupos de Produtos');
            $this->actingAs($user)->get(route('admin.product-groups.create'))->assertOk()->assertSeeText('Novo grupo de produtos');
            $this->actingAs($user)->get(route('admin.product-groups.show', $group))->assertOk()->assertSeeText('iPhone 16');
            $this->actingAs($user)->get(route('admin.product-groups.edit', $group))->assertOk()->assertSeeText('Editar grupo de produtos');
        }
    }

    public function test_valid_data_creates_group_with_generated_slug_and_nullable_description(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.product-groups.store'), [
            'name' => 'iPhone 16',
            'slug' => '',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'description' => '',
            'is_active' => '1',
        ]);

        $group = ProductGroup::query()->firstOrFail();
        $response->assertRedirectToRoute('admin.product-groups.show', $group)
            ->assertSessionHas('success', 'Grupo de produtos criado com sucesso.');
        $this->assertSame('iphone-16', $group->slug);
        $this->assertNull($group->description);
        $this->assertSame($category->id, $group->category_id);
        $this->assertSame($brand->id, $group->brand_id);
    }

    public function test_invalid_data_is_rejected(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('admin.product-groups.store'), [
            'name' => '',
            'slug' => 'Slug Inválido',
            'category_id' => 999,
            'brand_id' => 999,
            'is_active' => '1',
        ])->assertSessionHasErrors(['name', 'slug', 'category_id', 'brand_id']);

        $this->assertDatabaseCount('product_groups', 0);
    }

    public function test_group_can_be_updated(): void
    {
        $admin = $this->userWithRole('admin');
        $group = ProductGroup::factory()->create();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $this->actingAs($admin)->put(route('admin.product-groups.update', $group), [
            'name' => 'Galaxy S26',
            'slug' => 'galaxy-s26-ultra',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'description' => 'Linha premium.',
            'is_active' => '0',
        ])->assertRedirectToRoute('admin.product-groups.show', $group);

        $group->refresh();
        $this->assertSame('Galaxy S26', $group->name);
        $this->assertSame('galaxy-s26-ultra', $group->slug);
        $this->assertFalse($group->is_active);
    }

    public function test_group_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('admin');
        ProductGroup::factory()->create(['slug' => 'iphone-16']);
        $group = ProductGroup::factory()->make();

        $this->actingAs($admin)->post(route('admin.product-groups.store'), [
            ...$group->only(['name', 'category_id', 'brand_id']),
            'slug' => 'iphone-16',
            'is_active' => '1',
        ])->assertSessionHasErrors(['slug']);

        $this->assertDatabaseCount('product_groups', 1);
    }

    public function test_status_action_deactivates_and_reactivates_group(): void
    {
        $admin = $this->userWithRole('admin');
        $group = ProductGroup::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->patch(route('admin.product-groups.status', $group))->assertSessionHas('success');
        $this->assertFalse($group->refresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.product-groups.status', $group))->assertSessionHas('success');
        $this->assertTrue($group->refresh()->is_active);
    }

    public function test_filters_and_product_count_are_applied(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $matching = ProductGroup::factory()->for($category)->for($brand)->create(['name' => 'iPhone 16', 'is_active' => true]);
        Product::factory()->count(2)->for($matching)->for($category)->for($brand)->create();
        ProductGroup::factory()->create(['name' => 'Outro grupo', 'is_active' => false]);

        $response = $this->actingAs($admin)->get(route('admin.product-groups.index', [
            'q' => 'iPhone',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 'active',
        ]));

        $response->assertSeeText('iPhone 16')->assertDontSeeText('Outro grupo')
            ->assertViewHas('productGroups', fn ($groups): bool => $groups->total() === 1 && $groups->first()->products_count === 2);
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
