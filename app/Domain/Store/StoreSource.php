<?php

namespace App\Domain\Store;

use App\Domain\Catalog\Offer;
use App\Domain\Store\Enums\StoreSourceType;
use Database\Factories\Domain\Store\StoreSourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(StoreSourceFactory::class)]
#[Fillable(['store_id', 'type', 'provider_key', 'is_active', 'config'])]
class StoreSource extends Model
{
    /** @use HasFactory<StoreSourceFactory> */
    use HasFactory;

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => StoreSourceType::class,
            'is_active' => 'boolean',
            'config' => 'array',
        ];
    }
}
