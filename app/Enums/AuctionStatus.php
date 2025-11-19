<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;

enum AuctionStatus: string implements HasColor
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case LIVE = 'LIVE';
    case ENDED = 'ENDED';
    case PAYMENT_PENDING = 'PAYMENT_PENDING';
    case PAID = 'PAID';
    case OFFERED_TO_NEXT = 'OFFERED_TO_NEXT';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'info',
            self::LIVE => 'success',
            self::ENDED => 'danger',
            self::PAYMENT_PENDING => 'warning',
            self::PAID => 'success',
            self::OFFERED_TO_NEXT => 'info',
        };
    }
}
