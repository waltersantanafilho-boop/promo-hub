<?php

namespace App\Domain\Catalog\Enums;

enum OfferAvailability: string
{
    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::InStock => 'Em estoque',
            self::OutOfStock => 'Sem estoque',
            self::Unknown => 'Desconhecida',
        };
    }
}
