<?php

namespace Database\Factories\Domain\Catalog;

use App\Domain\Catalog\Offer;
use App\Domain\Catalog\PriceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceHistory>
 */
class PriceHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'offer_id' => Offer::factory(),
            'price' => $this->monetaryAmount(),
            'recorded_at' => now(),
        ];
    }

    private function monetaryAmount(): string
    {
        return sprintf(
            '%d.%04d',
            fake()->numberBetween(1, 100_000),
            fake()->numberBetween(0, 9_999),
        );
    }
}
