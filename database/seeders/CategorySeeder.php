<?php

namespace Database\Seeders;

use App\Domain\Catalog\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $electronics = Category::query()->firstOrCreate(
            ['slug' => 'eletronicos'],
            ['name' => 'Eletrônicos', 'is_active' => true],
        );

        $phones = Category::query()->firstOrCreate(
            ['slug' => 'celulares'],
            ['parent_id' => $electronics->id, 'name' => 'Celulares', 'is_active' => true],
        );

        Category::query()->firstOrCreate(
            ['slug' => 'smartphones'],
            ['parent_id' => $phones->id, 'name' => 'Smartphones', 'is_active' => true],
        );
    }
}
