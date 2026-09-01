<?php

namespace Database\Factories\Domain\Store;

use App\Domain\Store\Enums\StoreSourceType;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreSource>
 */
class StoreSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'type' => fake()->randomElement(StoreSourceType::cases()),
            'provider_key' => null,
            'is_active' => true,
            'config' => null,
        ];
    }
}
