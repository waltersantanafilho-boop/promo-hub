<?php

namespace App\Domain\Catalog\Enums;

enum OfferAvailability: string
{
    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';
    case Unknown = 'unknown';
}
