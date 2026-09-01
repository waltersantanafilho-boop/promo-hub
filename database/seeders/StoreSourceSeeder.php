<?php

namespace Database\Seeders;

use App\Domain\Store\Enums\StoreSourceType;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use Illuminate\Database\Seeder;

class StoreSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::query()->where('slug', 'loja-exemplo')->firstOrFail();

        StoreSource::query()->firstOrCreate(
            ['store_id' => $store->id, 'type' => StoreSourceType::Manual],
            [
                'provider_key' => null,
                'is_active' => true,
                'config' => ['description' => 'Cadastro manual'],
            ],
        );
    }
}
