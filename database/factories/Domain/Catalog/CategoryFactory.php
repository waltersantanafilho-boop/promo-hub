<?php

namespace Database\Factories\Domain\Catalog;

use App\Domain\Catalog\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(3),
            'is_active' => true,
        ];
    }
}
