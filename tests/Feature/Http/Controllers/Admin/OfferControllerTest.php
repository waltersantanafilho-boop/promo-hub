<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Domain\Catalog\Enums\OfferAvailability;
use App\Domain\Catalog\Enums\OfferStatus;
use App\Domain\Catalog\Offer;
use App\Domain\Catalog\Product;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use App\Models\User;
use App\Services\Catalog\OfferService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OfferControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_editor_without_manage_offers_cannot_access_offers(): void
    {
        $editor = $this->userWithRole('editor');

        $this->actingAs($editor)->get(route('admin.offers.index'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.offers.store'), [])->assertForbidden();
    }

    public function test_admin_can_access_all_offer_pages(): void
    {
        $admin = $this->userWithRole('admin');
        $offer = Offer::factory()->create();

        $this->actingAs($admin)->get(route('admin.offers.index'))->assertOk()->assertSeeText('Ofertas');
        $this->actingAs($admin)->get(route('admin.offers.create'))->assertOk()->assertSeeText('Nova oferta');
        $this->actingAs($admin)->get(route('admin.offers.show', $offer))->assertOk()->assertSeeText('Oferta #'.$offer->id);
        $this->actingAs($admin)->get(route('admin.offers.edit', $offer))->assertOk()->assertSeeText('Editar oferta');
    }

    public function test_admin_creation_uses_service_and_records_first_price_history(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();

        $response = $this->actingAs($admin)->post(route('admin.offers.store'), $this->validOfferData($product, $store, $source, '4599.90'));

        $offer = Offer::query()->firstOrFail();
        $response->assertRedirectToRoute('admin.offers.show', $offer)
            ->assertSessionHas('success', 'Oferta criada com sucesso e primeiro preço registrado.');
        $this->assertSame('4599.9000', $offer->price);
        $this->assertSame(1, $offer->priceHistories()->count());
        $this->assertSame('4599.9000', $offer->priceHistories()->firstOrFail()->price);
    }

    public function test_invalid_offer_data_is_rejected(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('admin.offers.store'), [
            'product_id' => 999,
            'store_id' => 999,
            'store_source_id' => 999,
            'external_id' => '',
            'url' => 'invalid',
            'price' => 'valor inválido',
            'currency' => 'REAL',
            'availability' => 'available',
            'status' => 'enabled',
        ])->assertSessionHasErrors(['product_id', 'store_id', 'store_source_id', 'external_id', 'url', 'price', 'currency', 'availability', 'status']);

        $this->assertDatabaseCount('offers', 0);
        $this->assertDatabaseCount('price_histories', 0);
    }

    public function test_store_source_must_belong_to_selected_store(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();
        $store = Store::factory()->create();
        $otherSource = StoreSource::factory()->create();

        $this->actingAs($admin)->post(route('admin.offers.store'), $this->validOfferData($product, $store, $otherSource))
            ->assertSessionHasErrors(['store_source_id' => 'A fonte selecionada não pertence à loja informada.']);

        $this->assertDatabaseCount('offers', 0);
    }

    public function test_external_id_must_be_unique_within_store_source(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();
        app(OfferService::class)->create($this->validOfferData($product, $store, $source));

        $this->actingAs($admin)->post(route('admin.offers.store'), $this->validOfferData($product, $store, $source))
            ->assertSessionHasErrors(['external_id']);

        $this->assertDatabaseCount('offers', 1);
        $this->assertDatabaseCount('price_histories', 1);
    }

    public function test_editing_without_price_change_does_not_add_history(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();
        $offer = app(OfferService::class)->create($this->validOfferData($product, $store, $source, '100.0000'));

        $this->actingAs($admin)->put(route('admin.offers.update', $offer), [
            ...$this->validOfferData($product, $store, $source, '100.00'),
            'url' => 'https://example.test/updated-offer',
        ])->assertRedirectToRoute('admin.offers.show', $offer)->assertSessionHas('success');

        $this->assertSame('https://example.test/updated-offer', $offer->refresh()->url);
        $this->assertSame('100.0000', $offer->price);
        $this->assertSame(1, $offer->priceHistories()->count());
    }

    public function test_editing_with_price_change_adds_exactly_one_history(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();
        $offer = app(OfferService::class)->create($this->validOfferData($product, $store, $source, '100.0000'));

        $this->actingAs($admin)->put(route('admin.offers.update', $offer), $this->validOfferData($product, $store, $source, '89.9000'))
            ->assertRedirectToRoute('admin.offers.show', $offer);

        $this->assertSame('89.9000', $offer->refresh()->price);
        $this->assertSame(2, $offer->priceHistories()->count());
        $this->assertSame(['100.0000', '89.9000'], $offer->priceHistories()->oldest('id')->pluck('price')->all());
    }

    public function test_monetary_values_remain_decimal_strings(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();

        $this->actingAs($admin)->post(route('admin.offers.store'), [
            ...$this->validOfferData($product, $store, $source, '1299.99'),
            'original_price' => '1499.9900',
        ]);

        $offer = Offer::query()->firstOrFail();
        $this->assertIsString($offer->price);
        $this->assertIsString($offer->original_price);
        $this->assertSame('1299.9900', $offer->price);
        $this->assertSame('1499.9900', $offer->original_price);
        $this->assertIsString($offer->priceHistories()->firstOrFail()->price);
    }

    public function test_brazilian_money_values_are_normalized_and_persisted_as_decimals(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();
        $values = [
            'R$ 0,01' => '0.0100',
            'R$ 12,34' => '12.3400',
            'R$ 4.199,00' => '4199.0000',
            'R$ 123.456,78' => '123456.7800',
        ];

        foreach ($values as $formatted => $persisted) {
            $data = $this->validOfferData($product, $store, $source, $formatted);
            $data['external_id'] = 'EXT-'.str_replace(['R$ ', '.', ','], '', $formatted);

            $this->actingAs($admin)->post(route('admin.offers.store'), $data)->assertSessionHasNoErrors();

            $offer = Offer::query()->where('external_id', $data['external_id'])->firstOrFail();
            $this->assertSame($persisted, $offer->price);
            $this->assertSame($persisted, $offer->priceHistories()->firstOrFail()->price);
        }
    }

    public function test_brazilian_original_price_is_normalized_and_persisted_as_decimal(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();

        $this->actingAs($admin)->post(route('admin.offers.store'), [
            ...$this->validOfferData($product, $store, $source, 'R$ 4.199,00'),
            'original_price' => 'R$ 4.699,00',
        ])->assertSessionHasNoErrors();

        $offer = Offer::query()->firstOrFail();
        $this->assertSame('4199.0000', $offer->price);
        $this->assertSame('4699.0000', $offer->original_price);
    }

    public function test_formatted_equivalent_price_does_not_add_history(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();
        $offer = app(OfferService::class)->create($this->validOfferData($product, $store, $source, '4199.0000'));

        $this->actingAs($admin)->put(route('admin.offers.update', $offer), $this->validOfferData($product, $store, $source, 'R$ 4.199,00'))
            ->assertSessionHasNoErrors();

        $this->assertSame('4199.0000', $offer->refresh()->price);
        $this->assertSame(1, $offer->priceHistories()->count());
    }

    public function test_existing_values_are_formatted_on_edit_form(): void
    {
        $admin = $this->userWithRole('admin');
        $offer = Offer::factory()->create([
            'price' => '4199.0000',
            'original_price' => '4699.0000',
        ]);

        $this->actingAs($admin)->get(route('admin.offers.edit', $offer))
            ->assertOk()
            ->assertSee('value="R$ 4.199,00"', false)
            ->assertSee('value="R$ 4.699,00"', false)
            ->assertDontSeeText('Use ponto como separador decimal.');
    }

    public function test_offer_status_can_be_changed_without_adding_history(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();
        $offer = app(OfferService::class)->create($this->validOfferData($product, $store, $source));

        $this->actingAs($admin)->patch(route('admin.offers.status', $offer), ['status' => 'expired'])
            ->assertRedirectToRoute('admin.offers.show', $offer);

        $this->assertSame(OfferStatus::Expired, $offer->refresh()->status);
        $this->assertSame(1, $offer->priceHistories()->count());
    }

    public function test_offer_filters_are_applied(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();
        Offer::factory()->for($product)->for($store)->for($source)->create([
            'external_id' => 'TARGET-123',
            'status' => OfferStatus::Expired,
            'availability' => OfferAvailability::OutOfStock,
        ]);
        Offer::factory()->create(['external_id' => 'OTHER-456']);

        $response = $this->actingAs($admin)->get(route('admin.offers.index', [
            'q' => 'TARGET',
            'product_id' => $product->id,
            'store_id' => $store->id,
            'status' => 'expired',
            'availability' => 'out_of_stock',
        ]));

        $response->assertSeeText('TARGET-123')->assertDontSeeText('OTHER-456')
            ->assertViewHas('offers', fn ($offers): bool => $offers->total() === 1);
    }

    public function test_offer_page_displays_recent_price_history(): void
    {
        $admin = $this->userWithRole('admin');
        [$product, $store, $source] = $this->offerRelations();
        $offerService = app(OfferService::class);
        $offer = $offerService->create($this->validOfferData($product, $store, $source, '300.0000'));
        $offerService->updatePrice($offer, '250.0000');

        $this->actingAs($admin)->get(route('admin.offers.show', $offer))
            ->assertSeeText(['Histórico de preços', $product->name, $store->name, 'R$ 300,00', 'R$ 250,00']);
    }

    /**
     * @return array{Product, Store, StoreSource}
     */
    private function offerRelations(): array
    {
        $product = Product::factory()->create();
        $store = Store::factory()->create();
        $source = StoreSource::factory()->for($store)->create();

        return [$product, $store, $source];
    }

    /**
     * @return array<string, mixed>
     */
    private function validOfferData(Product $product, Store $store, StoreSource $source, string $price = '100.0000'): array
    {
        return [
            'product_id' => $product->id,
            'store_id' => $store->id,
            'store_source_id' => $source->id,
            'external_id' => 'EXT-123',
            'url' => 'https://example.test/offer',
            'price' => $price,
            'original_price' => null,
            'currency' => 'BRL',
            'availability' => 'in_stock',
            'image_url' => null,
            'last_checked_at' => null,
            'status' => 'active',
        ];
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        return User::factory()->create()->assignRole($role);
    }
}
