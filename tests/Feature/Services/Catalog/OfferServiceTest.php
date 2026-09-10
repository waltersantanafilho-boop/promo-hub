<?php

namespace Tests\Feature\Services\Catalog;

use App\Domain\Catalog\Enums\OfferAvailability;
use App\Domain\Catalog\Enums\OfferStatus;
use App\Domain\Catalog\Product;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use App\Services\Catalog\OfferService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OfferServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creating_offer_immediately_records_first_price(): void
    {
        $this->travelTo('2026-09-01 10:00:00');

        $offer = app(OfferService::class)->create($this->offerAttributes('1999.9000'));

        $this->assertModelExists($offer);
        $this->assertCount(1, $offer->priceHistories);
        $this->assertSame('1999.9000', $offer->priceHistories->first()->price);
        $this->assertTrue($offer->priceHistories->first()->recorded_at->equalTo(now()));
    }

    public function test_equivalent_price_does_not_create_another_history(): void
    {
        $offerService = app(OfferService::class);
        $offer = $offerService->create($this->offerAttributes('100.0000'));

        $updatedOffer = $offerService->updatePrice($offer, '100.00');

        $this->assertSame('100.0000', $updatedOffer->price);
        $this->assertSame(1, $updatedOffer->priceHistories()->count());
    }

    public function test_changed_price_creates_another_history(): void
    {
        $offerService = app(OfferService::class);
        $this->travelTo('2026-09-01 10:00:00');
        $offer = $offerService->create($this->offerAttributes('100.0000'));

        $this->travelTo('2026-09-01 11:00:00');
        $updatedOffer = $offerService->updatePrice($offer, '95.5000');
        $priceHistories = $updatedOffer->priceHistories()->oldest('recorded_at')->get();

        $this->assertSame('95.5000', $updatedOffer->price);
        $this->assertCount(2, $priceHistories);
        $this->assertSame(['100.0000', '95.5000'], $priceHistories->pluck('price')->all());
        $this->assertSame(
            ['2026-09-01 10:00:00', '2026-09-01 11:00:00'],
            $priceHistories->map->recorded_at->map->toDateTimeString()->all(),
        );
    }

    /**
     * @return array{
     *     product_id: int,
     *     store_id: int,
     *     store_source_id: int,
     *     external_id: string,
     *     url: string,
     *     price: string,
     *     original_price: null,
     *     currency: string,
     *     availability: OfferAvailability,
     *     image_url: null,
     *     last_checked_at: null,
     *     status: OfferStatus
     * }
     */
    private function offerAttributes(string $price): array
    {
        $product = Product::factory()->create();
        $store = Store::factory()->create();
        $storeSource = StoreSource::factory()->for($store)->create();

        return [
            'product_id' => $product->id,
            'store_id' => $store->id,
            'store_source_id' => $storeSource->id,
            'external_id' => fake()->unique()->uuid(),
            'url' => 'https://example.com/product',
            'price' => $price,
            'original_price' => null,
            'currency' => 'BRL',
            'availability' => OfferAvailability::InStock,
            'image_url' => null,
            'last_checked_at' => null,
            'status' => OfferStatus::Active,
        ];
    }
}
