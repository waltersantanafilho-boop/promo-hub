<?php

namespace App\Domain\Store\Enums;

enum StoreSourceType: string
{
    case Api = 'api';
    case Feed = 'feed';
    case Manual = 'manual';
    case Scraper = 'scraper';

    public function label(): string
    {
        return match ($this) {
            self::Api => 'API',
            self::Feed => 'Feed',
            self::Manual => 'Manual',
            self::Scraper => 'Scraper',
        };
    }
}
