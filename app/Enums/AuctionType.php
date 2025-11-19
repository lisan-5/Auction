<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;

enum AuctionType: string implements HasColor
{
    case OPEN = 'OPEN';
    case CLOSED = 'CLOSED';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OPEN => 'success',
            self::CLOSED => 'gray',
        };
    }
}
