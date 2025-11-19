<?php

namespace App\Enums;

enum AuctionCondition: string
{
    case EXCELLENT = 'excellent';
    case GOOD = 'good';
    case FAIR = 'fair';
    case NEEDS_RESTORATION = 'needs_restoration';
}
