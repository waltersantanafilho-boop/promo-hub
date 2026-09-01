<?php

namespace Database\Factories\Domain\Store;

use App\Domain\Store\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(2),
            'logo_url' => fake()->url(),
            'website_url' => fake()->url(),
            'is_active' => true,
        ];
    }
}
