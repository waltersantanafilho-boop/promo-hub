<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enums\OfferAvailability;
use App\Domain\Catalog\Enums\OfferStatus;
use App\Domain\Catalog\Offer;
use App\Domain\Catalog\Product;
use App\Domain\Store\StoreSource;
use App\Services\Catalog\OfferService;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(OfferService $offerService): void
    {
        $product = Product::query()
            ->where('slug', 'smartphone-exemplo-128gb-preto')
            ->firstOrFail();
        $storeSource = StoreSource::query()
            ->where('provider_key', null)
            ->firstOrFail();

        if (Offer::query()
            ->where('store_source_id', $storeSource->id)
            ->where('external_id', 'manual-example-001')
            ->exists()) {
            return;
        }

        $offerService->create([
            'product_id' => $product->id,
            'store_id' => $storeSource->store_id,
            'store_source_id' => $storeSource->id,
            'external_id' => 'manual-example-001',
            'url' => 'https://example.com/products/smartphone-example',
            'price' => '1999.9000',
            'original_price' => '2199.9000',
            'currency' => 'BRL',
            'availability' => OfferAvailability::InStock,
            'image_url' => null,
            'last_checked_at' => now(),
            'status' => OfferStatus::Active,
        ]);
    }
}
