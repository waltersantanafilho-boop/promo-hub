<?php

namespace App\Domain\Catalog\Enums;

enum OfferStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Removed = 'removed';
}
