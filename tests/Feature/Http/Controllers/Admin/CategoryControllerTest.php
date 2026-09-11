<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Domain\Catalog\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_without_permission_cannot_read_or_create_categories(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.categories.store'), [
            'name' => 'Eletrônicos',
            'slug' => 'eletronicos',
            'is_active' => '1',
        ])->assertForbidden();

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_admin_can_access_all_category_pages(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create(['name' => 'Eletrônicos']);

        $this->actingAs($admin)->get(route('admin.categories.index'))->assertSeeText('Categorias');
        $this->actingAs($admin)->get(route('admin.categories.create'))->assertSeeText('Nova categoria');
        $this->actingAs($admin)->get(route('admin.categories.show', $category))->assertSeeText('Eletrônicos');
        $this->actingAs($admin)->get(route('admin.categories.edit', $category))->assertSeeText('Editar categoria');
    }

    public function test_editor_can_access_categories(): void
    {
        $editor = $this->userWithRole('editor');

        $this->actingAs($editor)->get(route('admin.categories.index'))
            ->assertSeeText('Categorias');
    }

    public function test_valid_data_creates_category_with_parent_and_generated_slug(): void
    {
        $admin = $this->userWithRole('admin');
        $parent = Category::factory()->create(['name' => 'Eletrônicos']);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Smartphones Premium',
            'slug' => '',
            'parent_id' => $parent->id,
            'is_active' => '1',
        ]);

        $category = Category::query()->where('slug', 'smartphones-premium')->firstOrFail();
        $response->assertRedirectToRoute('admin.categories.show', $category)
            ->assertSessionHas('success', 'Categoria criada com sucesso.');
        $this->assertSame($parent->id, $category->parent_id);
        $this->assertTrue($category->is_active);
    }

    public function test_invalid_data_returns_field_errors_without_creating_category(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->from(route('admin.categories.create'))->post(route('admin.categories.store'), [
            'name' => '',
            'slug' => 'Slug inválido!',
            'parent_id' => 999,
            'is_active' => '1',
        ])->assertRedirectToRoute('admin.categories.create')
            ->assertSessionHasErrors(['name', 'slug', 'parent_id']);

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_valid_data_updates_category_and_accepts_manual_slug(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create(['name' => 'Celulares', 'slug' => 'celulares']);

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Smartphones',
            'slug' => 'telefones-inteligentes',
            'parent_id' => '',
            'is_active' => '0',
        ]);

        $response->assertRedirectToRoute('admin.categories.show', $category)
            ->assertSessionHas('success', 'Categoria atualizada com sucesso.');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Smartphones',
            'slug' => 'telefones-inteligentes',
            'parent_id' => null,
            'is_active' => false,
        ]);
    }

    public function test_status_action_deactivates_and_reactivates_category(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->patch(route('admin.categories.status', $category))
            ->assertRedirectToRoute('admin.categories.index')
            ->assertSessionHas('success', 'Categoria desativada com sucesso.');
        $this->assertFalse($category->refresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.categories.status', $category))
            ->assertSessionHas('success', 'Categoria ativada com sucesso.');
        $this->assertTrue($category->refresh()->is_active);
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('admin');
        Category::factory()->create(['slug' => 'eletronicos']);

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Outra categoria',
            'slug' => 'eletronicos',
            'is_active' => '1',
        ])->assertSessionHasErrors(['slug']);

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_category_cannot_be_its_own_parent(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create();

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => $category->name,
            'slug' => $category->slug,
            'parent_id' => $category->id,
            'is_active' => '1',
        ])->assertSessionHasErrors([
            'parent_id' => 'A categoria pai não pode ser a própria categoria nem uma de suas descendentes.',
        ]);

        $this->assertNull($category->refresh()->parent_id);
    }

    public function test_category_cannot_use_a_descendant_as_parent(): void
    {
        $admin = $this->userWithRole('admin');
        $root = Category::factory()->create();
        $child = Category::factory()->for($root, 'parent')->create();
        Category::factory()->for($child, 'parent')->create();

        $this->actingAs($admin)->put(route('admin.categories.update', $root), [
            'name' => $root->name,
            'slug' => $root->slug,
            'parent_id' => $child->id,
            'is_active' => '1',
        ])->assertSessionHasErrors(['parent_id']);

        $this->assertNull($root->refresh()->parent_id);
    }

    public function test_index_shows_parent_and_uses_pagination(): void
    {
        $admin = $this->userWithRole('admin');
        $parent = Category::factory()->create(['name' => 'Categoria Pai']);
        Category::factory()->count(15)->for($parent, 'parent')->create();

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $response->assertSeeText('Categoria Pai')
            ->assertViewHas('categories', fn ($categories): bool => $categories->perPage() === 15 && $categories->total() === 16);
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        return User::factory()->create()->assignRole($role);
    }
}
