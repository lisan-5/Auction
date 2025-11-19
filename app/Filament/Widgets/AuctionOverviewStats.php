<?php

namespace App\Filament\Widgets;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuctionOverviewStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        
        $activeAuctions = Auction::where('status', 'LIVE')->count();
        $previousActiveAuctions = Auction::where('status', 'LIVE')
            ->where('created_at', '<', $today)
            ->count();

        $bidsToday = Bid::whereDate('created_at', $today)->count();
        $bidsYesterday = Bid::whereDate('created_at', $yesterday)->count();

        $volumeToday = Bid::whereDate('created_at', $today)->sum('bid_amount');
        $volumeYesterday = Bid::whereDate('created_at', $yesterday)->sum('bid_amount');

        $endingSoon = Auction::where('status', 'LIVE')
            ->where('end_time', '>', now())
            ->where('end_time', '<=', now()->addDay())
            ->count();

        // New artists this week
        $newArtists = User::role('artist')
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        return [
            Stat::make('Active Auctions', $activeAuctions)
                ->description('Currently live auctions')
                ->descriptionIcon('heroicon-m-signal')
                ->color('success')
                ->chart([7, 12, 8, 15, 18, 22, $activeAuctions]),

            Stat::make('Bids Today', $bidsToday)
                ->description($this->getComparisonText($bidsToday, $bidsYesterday, 'vs yesterday'))
                ->descriptionIcon($bidsToday >= $bidsYesterday ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($bidsToday >= $bidsYesterday ? 'success' : 'danger')
                ->chart([3, 8, 12, 6, 15, 18, $bidsToday]),

            Stat::make('Volume Today', 'ETB '.number_format($volumeToday, 2))
                ->description($this->getComparisonText($volumeToday, $volumeYesterday, 'vs yesterday'))
                ->descriptionIcon($volumeToday >= $volumeYesterday ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($volumeToday >= $volumeYesterday ? 'success' : 'danger')
                ->chart([1200, 2400, 1800, 3600, 2200, 4800, $volumeToday]),

            Stat::make('Ending Soon', $endingSoon)
                ->description('Next 24 hours')
                ->descriptionIcon('heroicon-m-clock')
                ->color($endingSoon > 5 ? 'warning' : 'info')
                ->chart([2, 4, 3, 6, 8, 5, $endingSoon]),

            Stat::make('New Artists', $newArtists)
                ->description('This week')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info')
                ->chart([1, 2, 0, 3, 1, 4, $newArtists]),
        ];
    }

    private function getComparisonText(float $current, float $previous, string $period): string
    {
        if ($previous == 0) {
            return $current > 0 ? "New activity {$period}" : "No activity {$period}";
        }

        $percentageChange = round((($current - $previous) / $previous) * 100, 1);
        $direction = $percentageChange >= 0 ? '+' : '';

        return "{$direction}{$percentageChange}% {$period}";
    }
}
