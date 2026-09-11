<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Domain\Store\Enums\StoreSourceType;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StoreSourceControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_without_permission_cannot_access_store_sources(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $store = Store::factory()->create();

        $this->actingAs($user)->get(route('admin.stores.sources.index', $store))->assertForbidden();
        $this->actingAs($user)->post(route('admin.stores.sources.store', $store), $this->validSourceData())->assertForbidden();

        $this->assertDatabaseCount('store_sources', 0);
    }

    public function test_admin_can_access_all_store_source_pages(): void
    {
        $admin = $this->admin();
        $store = Store::factory()->create(['name' => 'Amazon']);
        $source = StoreSource::factory()->for($store)->create(['type' => StoreSourceType::Api]);

        $this->actingAs($admin)->get(route('admin.stores.sources.index', $store))->assertSeeText('Fontes de Amazon');
        $this->actingAs($admin)->get(route('admin.stores.sources.create', $store))->assertSeeText('Nova fonte');
        $this->actingAs($admin)->get(route('admin.stores.sources.show', [$store, $source]))->assertSeeText('Fonte API');
        $this->actingAs($admin)->get(route('admin.stores.sources.edit', [$store, $source]))->assertSeeText('Editar fonte');
    }

    public function test_editor_cannot_access_store_sources(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $editor = User::factory()->create()->assignRole('editor');
        $store = Store::factory()->create();

        $this->actingAs($editor)->get(route('admin.stores.sources.index', $store))->assertForbidden();
    }

    public function test_valid_json_creates_source_for_route_store_and_ignores_submitted_store_id(): void
    {
        $admin = $this->admin();
        $store = Store::factory()->create();
        $otherStore = Store::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.stores.sources.store', $store), [
            ...$this->validSourceData(),
            'store_id' => $otherStore->id,
            'config' => '{"feed_url":"https://example.test/feed.json","timeout":30}',
        ]);

        $source = StoreSource::query()->firstOrFail();
        $response->assertRedirectToRoute('admin.stores.sources.show', [$store, $source])
            ->assertSessionHas('success', 'Fonte da loja criada com sucesso.');
        $this->assertSame($store->id, $source->store_id);
        $this->assertSame([
            'feed_url' => 'https://example.test/feed.json',
            'timeout' => 30,
        ], $source->config);
    }

    public function test_invalid_json_is_rejected_without_creating_source(): void
    {
        $admin = $this->admin();
        $store = Store::factory()->create();

        $this->actingAs($admin)->post(route('admin.stores.sources.store', $store), [
            ...$this->validSourceData(),
            'config' => '{invalid json}',
        ])->assertSessionHasErrors([
            'config' => 'Informe um JSON válido na configuração.',
        ]);

        $this->assertDatabaseCount('store_sources', 0);
    }

    public function test_sensitive_config_keys_are_rejected(): void
    {
        $admin = $this->admin();
        $store = Store::factory()->create();

        $this->actingAs($admin)->post(route('admin.stores.sources.store', $store), [
            ...$this->validSourceData(),
            'config' => '{"auth":{"access_token":"must-not-be-stored"}}',
        ])->assertSessionHasErrors([
            'config' => 'Não inclua secrets, tokens, senhas ou credenciais na configuração.',
        ]);

        $this->assertDatabaseCount('store_sources', 0);
    }

    public function test_valid_data_updates_source_without_changing_store(): void
    {
        $admin = $this->admin();
        $store = Store::factory()->create();
        $otherStore = Store::factory()->create();
        $source = StoreSource::factory()->for($store)->create([
            'type' => StoreSourceType::Manual,
            'config' => null,
        ]);

        $this->actingAs($admin)->put(route('admin.stores.sources.update', [$store, $source]), [
            'store_id' => $otherStore->id,
            'type' => 'feed',
            'provider_key' => 'catalog.feed',
            'config' => '[{"url":"https://example.test/feed.json"}]',
            'is_active' => '0',
        ])->assertRedirectToRoute('admin.stores.sources.show', [$store, $source])
            ->assertSessionHas('success', 'Fonte da loja atualizada com sucesso.');

        $source->refresh();
        $this->assertSame($store->id, $source->store_id);
        $this->assertSame(StoreSourceType::Feed, $source->type);
        $this->assertSame([['url' => 'https://example.test/feed.json']], $source->config);
        $this->assertFalse($source->is_active);
    }

    public function test_status_action_deactivates_source(): void
    {
        $admin = $this->admin();
        $store = Store::factory()->create();
        $source = StoreSource::factory()->for($store)->create(['is_active' => true]);

        $this->actingAs($admin)->patch(route('admin.stores.sources.status', [$store, $source]))
            ->assertRedirectToRoute('admin.stores.sources.index', $store)
            ->assertSessionHas('success', 'Fonte desativada com sucesso.');

        $this->assertFalse($source->refresh()->is_active);
    }

    public function test_routes_do_not_expose_or_modify_source_from_another_store(): void
    {
        $admin = $this->admin();
        $store = Store::factory()->create();
        $otherStore = Store::factory()->create();
        $source = StoreSource::factory()->for($otherStore)->create(['is_active' => true]);

        $this->actingAs($admin)->get(route('admin.stores.sources.show', [$store, $source]))->assertNotFound();
        $this->actingAs($admin)->get(route('admin.stores.sources.edit', [$store, $source]))->assertNotFound();
        $this->actingAs($admin)->put(route('admin.stores.sources.update', [$store, $source]), $this->validSourceData())->assertNotFound();
        $this->actingAs($admin)->patch(route('admin.stores.sources.status', [$store, $source]))->assertNotFound();

        $this->assertTrue($source->refresh()->is_active);
        $this->assertSame($otherStore->id, $source->store_id);
    }

    public function test_invalid_type_and_provider_key_are_rejected(): void
    {
        $admin = $this->admin();
        $store = Store::factory()->create();

        $this->actingAs($admin)->post(route('admin.stores.sources.store', $store), [
            'type' => 'webhook',
            'provider_key' => 'INVALID KEY',
            'config' => '',
            'is_active' => '1',
        ])->assertSessionHasErrors(['type', 'provider_key']);

        $this->assertDatabaseCount('store_sources', 0);
    }

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        return User::factory()->create()->assignRole('admin');
    }

    /**
     * @return array<string, string>
     */
    private function validSourceData(): array
    {
        return [
            'type' => 'api',
            'provider_key' => 'example_api',
            'config' => '',
            'is_active' => '1',
        ];
    }
}
