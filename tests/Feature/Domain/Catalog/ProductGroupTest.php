<?php

namespace Tests\Feature\Domain\Catalog;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductGroup;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductGroupTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_group_belongs_to_category_and_brand_and_contains_products(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $productGroup = ProductGroup::factory()
            ->for($category)
            ->for($brand)
            ->create(['description' => null, 'is_active' => 1]);
        $product = Product::factory()
            ->for($productGroup)
            ->for($category)
            ->for($brand)
            ->create();

        $this->assertTrue($productGroup->category->is($category));
        $this->assertTrue($productGroup->brand->is($brand));
        $this->assertTrue($productGroup->products->contains($product));
        $this->assertNull($productGroup->description);
        $this->assertTrue($productGroup->is_active);
    }

    public function test_duplicate_slugs_are_rejected(): void
    {
        ProductGroup::factory()->create(['slug' => 'iphone-16']);

        $this->expectException(QueryException::class);

        ProductGroup::factory()->create(['slug' => 'iphone-16']);
    }
}
