<?php

namespace App\Domain\Catalog\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Merged = 'merged';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Merged => 'Mesclado',
            self::Archived => 'Arquivado',
        };
    }
}
