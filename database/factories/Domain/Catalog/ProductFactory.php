<?php

namespace Database\Factories\Domain\Catalog;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_group_id' => null,
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(4),
            'description' => fake()->paragraph(),
            'gtin' => fake()->optional()->ean13(),
            'attributes' => null,
            'canonical_image_url' => null,
            'status' => ProductStatus::Active,
            'merged_into_id' => null,
        ];
    }
}
