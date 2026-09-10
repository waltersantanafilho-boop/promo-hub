<?php

namespace Database\Seeders;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\ProductGroup;
use Illuminate\Database\Seeder;

class ProductGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category = Category::query()->where('slug', 'smartphones')->firstOrFail();
        $brand = Brand::query()->where('slug', 'marca-exemplo')->firstOrFail();

        ProductGroup::query()->firstOrCreate(
            ['slug' => 'smartphone-exemplo'],
            [
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => 'Smartphone Exemplo',
                'description' => 'Grupo demonstrativo de variações de smartphone.',
                'is_active' => true,
            ],
        );
    }
}
