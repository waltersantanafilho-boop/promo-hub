<?php

namespace App\Services\Catalog;

use App\Domain\Catalog\Enums\OfferAvailability;
use App\Domain\Catalog\Enums\OfferStatus;
use App\Domain\Catalog\Offer;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OfferService
{
    /**
     * @param  array{
     *     product_id: int,
     *     store_id: int,
     *     store_source_id: int,
     *     external_id: string,
     *     url: string,
     *     price: string,
     *     original_price?: string|null,
     *     currency?: string,
     *     availability?: OfferAvailability|string,
     *     image_url?: string|null,
     *     last_checked_at?: DateTimeInterface|string|null,
     *     status?: OfferStatus|string
     * }  $attributes
     */
    public function create(array $attributes): Offer
    {
        return DB::transaction(function () use ($attributes): Offer {
            $offer = Offer::query()->create($attributes);

            $offer->priceHistories()->create([
                'price' => $offer->price,
                'recorded_at' => now(),
            ]);

            return $offer;
        });
    }

    public function updatePrice(Offer $offer, string $price): Offer
    {
        return DB::transaction(function () use ($offer, $price): Offer {
            $offer->price = $price;

            if (! $offer->isDirty('price')) {
                return $offer;
            }

            $offer->save();

            $offer->priceHistories()->create([
                'price' => $offer->price,
                'recorded_at' => now(),
            ]);

            return $offer;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Offer $offer, array $attributes): Offer
    {
        return DB::transaction(function () use ($offer, $attributes): Offer {
            $this->updatePrice($offer, (string) $attributes['price']);
            $offer->update(Arr::except($attributes, ['price']));

            return $offer->refresh();
        });
    }
}
