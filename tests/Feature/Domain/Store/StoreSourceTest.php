<?php

namespace Tests\Feature\Domain\Store;

use App\Domain\Store\Enums\StoreSourceType;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StoreSourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_store_relation_and_source_casts_are_available(): void
    {
        $store = Store::factory()->create();
        $source = StoreSource::factory()->for($store)->create([
            'type' => StoreSourceType::Api,
            'provider_key' => null,
            'is_active' => 0,
            'config' => [
                'timeout' => 15,
                'catalog_url' => 'https://example.com/catalog.json',
            ],
        ]);

        $this->assertTrue($source->store->is($store));
        $this->assertSame(StoreSourceType::Api, $source->type);
        $this->assertFalse($source->is_active);
        $this->assertSame([
            'timeout' => 15,
            'catalog_url' => 'https://example.com/catalog.json',
        ], $source->config);
        $this->assertNull($source->provider_key);
    }

    public function test_unknown_source_types_are_rejected(): void
    {
        $store = Store::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('store_sources')->insert([
            'store_id' => $store->id,
            'type' => 'unknown',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_source_requires_an_existing_store(): void
    {
        $this->expectException(QueryException::class);

        StoreSource::factory()->create(['store_id' => PHP_INT_MAX]);
    }
}
