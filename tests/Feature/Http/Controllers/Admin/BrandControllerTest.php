<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Domain\Catalog\Brand;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BrandControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_without_permission_cannot_access_brands(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.brands.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.brands.store'), [
            'name' => 'Acme',
            'slug' => 'acme',
            'is_active' => '1',
        ])->assertForbidden();

        $this->assertDatabaseCount('brands', 0);
    }

    public function test_admin_and_editor_can_access_brands(): void
    {
        $admin = $this->userWithRole('admin');
        $editor = User::factory()->create()->assignRole('editor');
        $brand = Brand::factory()->create(['name' => 'Acme']);

        $this->actingAs($admin)->get(route('admin.brands.create'))->assertSeeText('Nova marca');
        $this->actingAs($admin)->get(route('admin.brands.show', $brand))->assertSeeText('Acme');
        $this->actingAs($admin)->get(route('admin.brands.edit', $brand))->assertSeeText('Editar marca');
        $this->actingAs($editor)->get(route('admin.brands.index'))->assertSeeText('Marcas');
    }

    public function test_valid_data_creates_brand_and_generates_slug(): void
    {
        $admin = $this->userWithRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.brands.store'), [
            'name' => 'São João Tech',
            'slug' => '',
            'logo_url' => 'https://example.test/logo.png',
            'is_active' => '1',
        ]);

        $brand = Brand::query()->where('slug', 'sao-joao-tech')->firstOrFail();
        $response->assertRedirectToRoute('admin.brands.show', $brand)
            ->assertSessionHas('success', 'Marca criada com sucesso.');
        $this->assertSame('https://example.test/logo.png', $brand->logo_url);
    }

    public function test_invalid_data_returns_errors_without_creating_brand(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('admin.brands.store'), [
            'name' => '',
            'slug' => 'inválido!',
            'logo_url' => 'javascript:alert(1)',
            'is_active' => '1',
        ])->assertSessionHasErrors(['name', 'slug', 'logo_url']);

        $this->assertDatabaseCount('brands', 0);
    }

    public function test_valid_data_updates_brand(): void
    {
        $admin = $this->userWithRole('admin');
        $brand = Brand::factory()->create(['name' => 'Antes', 'slug' => 'antes']);

        $this->actingAs($admin)->put(route('admin.brands.update', $brand), [
            'name' => 'Depois',
            'slug' => 'marca-editada',
            'logo_url' => '',
            'is_active' => '0',
        ])->assertRedirectToRoute('admin.brands.show', $brand)
            ->assertSessionHas('success', 'Marca atualizada com sucesso.');

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Depois',
            'slug' => 'marca-editada',
            'logo_url' => null,
            'is_active' => false,
        ]);
    }

    public function test_status_action_deactivates_brand(): void
    {
        $admin = $this->userWithRole('admin');
        $brand = Brand::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->patch(route('admin.brands.status', $brand))
            ->assertRedirectToRoute('admin.brands.index')
            ->assertSessionHas('success', 'Marca desativada com sucesso.');

        $this->assertFalse($brand->refresh()->is_active);
    }

    public function test_slug_must_be_unique_except_for_current_brand(): void
    {
        $admin = $this->userWithRole('admin');
        $first = Brand::factory()->create(['slug' => 'acme']);
        $second = Brand::factory()->create(['slug' => 'other']);

        $this->actingAs($admin)->put(route('admin.brands.update', $second), [
            'name' => 'Other',
            'slug' => 'acme',
            'logo_url' => '',
            'is_active' => '1',
        ])->assertSessionHasErrors(['slug']);

        $this->actingAs($admin)->put(route('admin.brands.update', $first), [
            'name' => 'Acme Updated',
            'slug' => 'acme',
            'logo_url' => '',
            'is_active' => '1',
        ])->assertRedirectToRoute('admin.brands.show', $first);
    }

    public function test_index_is_paginated_and_escapes_brand_name(): void
    {
        $admin = $this->userWithRole('admin');
        Brand::factory()->count(15)->create();
        $dangerousName = '<script>alert("brand")</script>';
        Brand::factory()->create(['name' => $dangerousName]);

        $response = $this->actingAs($admin)->get(route('admin.brands.index', ['q' => '<script>']));

        $response->assertSee($dangerousName)
            ->assertDontSee($dangerousName, false)
            ->assertViewHas('brands', fn ($brands): bool => $brands->perPage() === 15 && $brands->total() === 1);
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        return User::factory()->create()->assignRole($role);
    }
}
