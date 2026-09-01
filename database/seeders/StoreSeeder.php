<?php

namespace Database\Seeders;

use App\Domain\Store\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Store::query()->firstOrCreate(
            ['slug' => 'loja-exemplo'],
            [
                'name' => 'Loja Exemplo',
                'logo_url' => 'https://example.com/logo.svg',
                'website_url' => 'https://example.com',
                'is_active' => true,
            ],
        );
    }
}
