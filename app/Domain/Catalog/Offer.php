<?php

namespace App\Domain\Catalog;

use App\Domain\Catalog\Enums\OfferAvailability;
use App\Domain\Catalog\Enums\OfferStatus;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use Database\Factories\Domain\Catalog\OfferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(OfferFactory::class)]
#[Fillable([
    'product_id',
    'store_id',
    'store_source_id',
    'external_id',
    'url',
    'price',
    'original_price',
    'currency',
    'availability',
    'image_url',
    'last_checked_at',
    'status',
])]
class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function storeSource(): BelongsTo
    {
        return $this->belongsTo(StoreSource::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'original_price' => 'decimal:4',
            'availability' => OfferAvailability::class,
            'last_checked_at' => 'datetime',
            'status' => OfferStatus::class,
        ];
    }
}
