<?php

namespace Database\Factories\Domain\Catalog;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\ProductGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductGroup>
 */
class ProductGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(3),
            'description' => fake()->optional()->paragraph(),
            'is_active' => true,
        ];
    }
}
