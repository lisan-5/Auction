<?php

namespace App\Filament\Resources\Auctions\Pages;

use App\Enums\AuctionType;
use App\Filament\Resources\Auctions\AuctionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAuctions extends ListRecords
{
    protected static string $resource = AuctionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getHeading(): string
    {
        return 'All Auctions';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'open' => Tab::make('Open')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', AuctionType::OPEN->value)),
            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', AuctionType::CLOSED->value)),
        ];
    }
}
