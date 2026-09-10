<?php

namespace Tests\Feature\Domain\Catalog;

use App\Domain\Catalog\Offer;
use App\Domain\Catalog\PriceHistory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PriceHistoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_history_belongs_to_offer_and_casts_price_and_recorded_time(): void
    {
        $this->travelTo('2026-09-01 10:00:00');
        $offer = Offer::factory()->create();
        $priceHistory = PriceHistory::factory()->for($offer)->create([
            'price' => '1499.9900',
            'recorded_at' => now(),
        ]);

        $this->assertTrue($priceHistory->offer->is($offer));
        $this->assertSame('1499.9900', $priceHistory->price);
        $this->assertTrue($priceHistory->recorded_at->equalTo(now()));
    }
}
