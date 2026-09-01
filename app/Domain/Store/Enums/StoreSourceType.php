<?php

namespace App\Domain\Store\Enums;

enum StoreSourceType: string
{
    case Api = 'api';
    case Feed = 'feed';
    case Manual = 'manual';
    case Scraper = 'scraper';
}
