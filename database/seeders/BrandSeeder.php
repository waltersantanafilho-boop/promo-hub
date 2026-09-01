<?php

namespace Database\Seeders;

use App\Domain\Catalog\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Brand::query()->firstOrCreate(
            ['slug' => 'marca-exemplo'],
            ['name' => 'Marca Exemplo', 'is_active' => true],
        );
    }
}
