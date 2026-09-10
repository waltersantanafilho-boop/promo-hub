<?php

namespace Tests\Feature\Domain\Catalog;

use App\Domain\Catalog\Enums\OfferAvailability;
use App\Domain\Catalog\Enums\OfferStatus;
use App\Domain\Catalog\Offer;
use App\Domain\Catalog\Product;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OfferTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_offer_belongs_to_product_store_and_store_source(): void
    {
        $product = Product::factory()->create();
        $store = Store::factory()->create();
        $storeSource = StoreSource::factory()->for($store)->create();
        $offer = Offer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'store_source_id' => $storeSource->id,
            'price' => '1999.9000',
            'original_price' => '2199.9000',
            'availability' => OfferAvailability::InStock,
            'status' => OfferStatus::Active,
        ]);

        $this->assertTrue($offer->product->is($product));
        $this->assertTrue($offer->store->is($store));
        $this->assertTrue($offer->storeSource->is($storeSource));
        $this->assertSame('1999.9000', $offer->price);
        $this->assertSame('2199.9000', $offer->original_price);
        $this->assertSame('BRL', $offer->currency);
        $this->assertSame(OfferAvailability::InStock, $offer->availability);
        $this->assertSame(OfferStatus::Active, $offer->status);
    }

    public function test_external_id_is_unique_within_a_store_source(): void
    {
        $store = Store::factory()->create();
        $storeSource = StoreSource::factory()->for($store)->create();
        $attributes = [
            'store_id' => $store->id,
            'store_source_id' => $storeSource->id,
            'external_id' => 'SKU-123',
        ];
        Offer::factory()->create($attributes);

        $this->expectException(QueryException::class);

        Offer::factory()->create($attributes);
    }
}
