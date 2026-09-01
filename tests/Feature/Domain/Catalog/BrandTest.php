<?php

namespace Tests\Feature\Domain\Catalog;

use App\Domain\Catalog\Brand;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_active_state_is_cast_to_boolean(): void
    {
        $brand = Brand::factory()->create(['is_active' => 0]);

        $this->assertFalse($brand->is_active);
    }

    public function test_duplicate_slugs_are_rejected(): void
    {
        Brand::factory()->create(['slug' => 'marca-exemplo']);

        $this->expectException(QueryException::class);

        Brand::factory()->create(['slug' => 'marca-exemplo']);
    }
}
