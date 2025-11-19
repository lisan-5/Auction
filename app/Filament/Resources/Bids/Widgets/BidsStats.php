<?php

namespace App\Filament\Resources\Bids\Widgets;

use App\Enums\AuctionStatus;
use App\Models\Bid;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class BidsStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Use a single query to get all statistics efficiently (excluding trashed bids)
        $stats = Bid::query()
            ->withoutTrashed()
            ->selectRaw('
                COUNT(*) as total_bids,
                SUM(bid_amount) as total_volume,
                AVG(bid_amount) as average_bid,
                MAX(bid_amount) as highest_bid
            ')
            ->first();

        $liveBidsCount = Bid::query()
            ->withoutTrashed()
            ->whereHas('auction', fn ($query) => $query->where('status', AuctionStatus::LIVE))
            ->count();

        return [
            Stat::make('Total Bids', Number::format($stats->total_bids ?? 0))
                ->description('All bids placed')
                ->descriptionIcon('heroicon-m-rectangle-stack', IconPosition::Before)
                ->color('primary'),

            Stat::make('Live Bids', Number::format($liveBidsCount))
                ->description('Bids on live auctions')
                ->descriptionIcon('heroicon-m-bolt', IconPosition::Before)
                ->color('success'),

            Stat::make('Total Volume', 'ETB '.Number::format($stats->total_volume ?? 0, 2))
                ->description('Total bid value')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::Before)
                ->color('warning'),

            Stat::make('Average Bid', 'ETB '.Number::format($stats->average_bid ?? 0, 2))
                ->description('Mean bid amount')
                ->descriptionIcon('heroicon-m-calculator', IconPosition::Before)
                ->color('info'),
        ];
    }
}
