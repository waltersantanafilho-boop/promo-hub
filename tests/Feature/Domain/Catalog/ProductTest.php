<?php

namespace Tests\Feature\Domain\Catalog;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Offer;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductGroup;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_product_relations_and_domain_casts_are_available(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $productGroup = ProductGroup::factory()
            ->for($category)
            ->for($brand)
            ->create();
        $product = Product::factory()
            ->for($productGroup)
            ->for($category)
            ->for($brand)
            ->create([
                'attributes' => ['storage' => '256GB', 'color' => 'Preto'],
                'status' => ProductStatus::Active,
            ]);

        $this->assertTrue($product->productGroup->is($productGroup));
        $this->assertTrue($product->category->is($category));
        $this->assertTrue($product->brand->is($brand));
        $this->assertSame(['storage' => '256GB', 'color' => 'Preto'], $product->attributes);
        $this->assertSame(ProductStatus::Active, $product->status);
    }

    public function test_product_exposes_its_offers(): void
    {
        $product = Product::factory()->create();
        $offer = Offer::factory()->for($product)->create();

        $this->assertTrue($product->offers->contains($offer));
    }

    public function test_merged_product_points_to_the_canonical_product(): void
    {
        $canonicalProduct = Product::factory()->create();
        $mergedProduct = Product::factory()->for($canonicalProduct, 'mergedInto')->create([
            'status' => ProductStatus::Merged,
        ]);

        $this->assertTrue($mergedProduct->mergedInto->is($canonicalProduct));
        $this->assertTrue($canonicalProduct->mergedProducts->contains($mergedProduct));
        $this->assertSame(ProductStatus::Merged, $mergedProduct->status);
    }

    public function test_duplicate_slugs_are_rejected(): void
    {
        Product::factory()->create(['slug' => 'iphone-16-128gb-preto']);

        $this->expectException(QueryException::class);

        Product::factory()->create(['slug' => 'iphone-16-128gb-preto']);
    }
}
