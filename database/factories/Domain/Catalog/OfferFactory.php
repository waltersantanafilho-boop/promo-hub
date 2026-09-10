<?php

namespace Database\Factories\Domain\Catalog;

use App\Domain\Catalog\Enums\OfferAvailability;
use App\Domain\Catalog\Enums\OfferStatus;
use App\Domain\Catalog\Offer;
use App\Domain\Catalog\Product;
use App\Domain\Store\StoreSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'store_source_id' => StoreSource::factory(),
            'store_id' => fn (array $attributes): int => StoreSource::query()
                ->findOrFail($attributes['store_source_id'])
                ->store_id,
            'external_id' => fake()->unique()->uuid(),
            'url' => fake()->url(),
            'price' => $this->monetaryAmount(),
            'original_price' => null,
            'currency' => 'BRL',
            'availability' => OfferAvailability::Unknown,
            'image_url' => null,
            'last_checked_at' => null,
            'status' => OfferStatus::Active,
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
