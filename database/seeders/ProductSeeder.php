<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductGroup;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productGroup = ProductGroup::query()
            ->where('slug', 'smartphone-exemplo')
            ->firstOrFail();

        Product::query()->firstOrCreate(
            ['slug' => 'smartphone-exemplo-128gb-preto'],
            [
                'product_group_id' => $productGroup->id,
                'category_id' => $productGroup->category_id,
                'brand_id' => $productGroup->brand_id,
                'name' => 'Smartphone Exemplo 128GB Preto',
                'description' => 'Produto demonstrativo da fundação do catálogo.',
                'gtin' => null,
                'attributes' => ['storage' => '128GB', 'color' => 'Preto'],
                'canonical_image_url' => null,
                'status' => ProductStatus::Active,
                'merged_into_id' => null,
            ],
        );
    }
}
