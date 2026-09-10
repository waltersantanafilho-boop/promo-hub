<?php

namespace App\Domain\Catalog\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Merged = 'merged';
    case Archived = 'archived';
}
