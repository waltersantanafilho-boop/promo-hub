<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_without_permission_cannot_access_stores(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.stores.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.stores.store'), $this->validStoreData())->assertForbidden();

        $this->assertDatabaseCount('stores', 0);
    }

    public function test_admin_can_access_all_store_pages(): void
    {
        $admin = $this->userWithRole('admin');
        $store = Store::factory()->create(['name' => 'Loja Acme']);

        $this->actingAs($admin)->get(route('admin.stores.index'))->assertSeeText('Lojas');
        $this->actingAs($admin)->get(route('admin.stores.create'))->assertSeeText('Nova loja');
        $this->actingAs($admin)->get(route('admin.stores.show', $store))->assertSeeText('Loja Acme');
        $this->actingAs($admin)->get(route('admin.stores.edit', $store))->assertSeeText('Editar loja');
    }

    public function test_editor_cannot_access_stores_without_manage_stores_permission(): void
    {
        $editor = $this->userWithRole('editor');

        $this->actingAs($editor)->get(route('admin.stores.index'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.stores.store'), $this->validStoreData())->assertForbidden();

        $this->assertDatabaseCount('stores', 0);
    }

    public function test_valid_data_creates_store_with_generated_slug(): void
    {
        $admin = $this->userWithRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.stores.store'), [
            ...$this->validStoreData(),
            'name' => 'Mercado São Paulo',
            'slug' => '',
        ]);

        $store = Store::query()->where('slug', 'mercado-sao-paulo')->firstOrFail();
        $response->assertRedirectToRoute('admin.stores.show', $store)
            ->assertSessionHas('success', 'Loja criada com sucesso.');
        $this->assertTrue($store->is_active);
    }

    public function test_invalid_data_returns_errors_without_creating_store(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('admin.stores.store'), [
            'name' => '',
            'slug' => 'slug inválido',
            'logo_url' => 'not-a-url',
            'website_url' => 'ftp://example.test',
            'is_active' => '1',
        ])->assertSessionHasErrors(['name', 'slug', 'logo_url', 'website_url']);

        $this->assertDatabaseCount('stores', 0);
    }

    public function test_valid_data_updates_store(): void
    {
        $admin = $this->userWithRole('admin');
        $store = Store::factory()->create(['slug' => 'old-store']);

        $this->actingAs($admin)->put(route('admin.stores.update', $store), [
            ...$this->validStoreData(),
            'name' => 'Loja Atualizada',
            'slug' => 'loja-atualizada',
            'is_active' => '0',
        ])->assertRedirectToRoute('admin.stores.show', $store)
            ->assertSessionHas('success', 'Loja atualizada com sucesso.');

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => 'Loja Atualizada',
            'slug' => 'loja-atualizada',
            'is_active' => false,
        ]);
    }

    public function test_status_action_deactivates_store_without_removing_sources(): void
    {
        $admin = $this->userWithRole('admin');
        $store = Store::factory()->create(['is_active' => true]);
        $source = StoreSource::factory()->for($store)->create();

        $this->actingAs($admin)->patch(route('admin.stores.status', $store))
            ->assertRedirectToRoute('admin.stores.index')
            ->assertSessionHas('success', 'Loja desativada com sucesso.');

        $this->assertFalse($store->refresh()->is_active);
        $this->assertModelExists($source);
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('admin');
        Store::factory()->create(['slug' => 'acme']);

        $this->actingAs($admin)->post(route('admin.stores.store'), [
            ...$this->validStoreData(),
            'slug' => 'acme',
        ])->assertSessionHasErrors(['slug']);

        $this->assertDatabaseCount('stores', 1);
    }

    public function test_index_uses_pagination_and_shows_source_count(): void
    {
        $admin = $this->userWithRole('admin');
        $store = Store::factory()->create(['name' => 'Loja com Fontes']);
        StoreSource::factory()->count(2)->for($store)->create();
        Store::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get(route('admin.stores.index'));

        $response->assertSeeText('Loja com Fontes')
            ->assertViewHas('stores', fn ($stores): bool => $stores->perPage() === 15 && $stores->total() === 16);
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        return User::factory()->create()->assignRole($role);
    }

    /**
     * @return array<string, string>
     */
    private function validStoreData(): array
    {
        return [
            'name' => 'Loja Acme',
            'slug' => 'loja-acme',
            'logo_url' => 'https://example.test/logo.png',
            'website_url' => 'https://example.test',
            'is_active' => '1',
        ];
    }
}
