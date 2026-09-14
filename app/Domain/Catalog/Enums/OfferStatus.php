<?php

namespace App\Domain\Catalog\Enums;

enum OfferStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Removed = 'removed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativa',
            self::Expired => 'Expirada',
            self::Removed => 'Removida',
        };
    }
}
