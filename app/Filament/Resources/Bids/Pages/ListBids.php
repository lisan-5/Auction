<?php

namespace App\Filament\Resources\Bids\Pages;

use App\Enums\AuctionStatus;
use App\Filament\Resources\Bids\BidResource;
use App\Filament\Resources\Bids\Widgets\BidsStats;
use App\Models\Bid;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBids extends ListRecords
{
    protected static string $resource = BidResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action; bids are read-only in admin.
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BidsStats::class,
        ];
    }

    public function getTitle(): string
    {
        return 'All Bids';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn () => Bid::withoutTrashed()->count()),
            'live' => Tab::make('Live')
                ->badge(fn () => Bid::withoutTrashed()->whereHas('auction', fn (Builder $query) => $query->where('status', AuctionStatus::LIVE))->count())
                ->modifyQueryUsing(function (Builder $query): Builder {
                    return $query->whereHas('auction', fn (Builder $q) => $q->where('status', AuctionStatus::LIVE));
                }),
            'with_deleted' => Tab::make('With Deleted')
                ->badge(fn () => Bid::withTrashed()->count())
                ->modifyQueryUsing(function (Builder $query): Builder {
                    return $query->withTrashed();
                }),
        ];
    }
}
