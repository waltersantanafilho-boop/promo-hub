<?php

namespace Tests\Feature\Domain\Store;

use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_sources_expose_the_store_technical_channels(): void
    {
        $store = Store::factory()->create(['is_active' => 1]);
        $source = StoreSource::factory()->for($store)->create();

        $this->assertTrue($store->sources->contains($source));
        $this->assertTrue($store->is_active);
    }

    public function test_store_with_sources_cannot_be_deleted(): void
    {
        $store = Store::factory()->create();
        StoreSource::factory()->for($store)->create();

        $this->expectException(QueryException::class);

        $store->delete();
    }

    public function test_duplicate_slugs_are_rejected(): void
    {
        Store::factory()->create(['slug' => 'loja-exemplo']);

        $this->expectException(QueryException::class);

        Store::factory()->create(['slug' => 'loja-exemplo']);
    }
}
