<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Auctions\AuctionResource;
use App\Models\Auction;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ActiveAuctionsTable extends TableWidget
{
    protected static ?string $heading = 'Active Auctions';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Auction::query()
                    ->where('status', 'LIVE')
                    ->with(['artist', 'category'])
                    ->orderBy('end_time', 'asc')
            )
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('')
                    ->getStateUsing(fn (Auction $record): ?string => $record->thumbnail_url)
                    ->width(60)
                    ->height(60)
                    ->circular(),

                TextColumn::make('title')
                    ->label('Auction')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (Auction $record): string => $record->title),

                TextColumn::make('artist.name')
                    ->label('Artist')
                    ->searchable()
                    ->limit(20),

                TextColumn::make('current_highest_bid')
                    ->label('Current Bid')
                    ->formatStateUsing(fn (Auction $record): string => 'ETB '.number_format($record->currentHighestBid(), 2))
                    ->weight(FontWeight::Medium)
                    ->color('success'),

                TextColumn::make('bids_count')
                    ->label('Bids')
                    ->counts('bids')
                    ->badge()
                    ->color('info'),

                TextColumn::make('end_time')
                    ->label('Ends')
                    ->since()
                    ->color(fn (Auction $record) => $record->end_time->diffInHours() < 2 ? 'danger' :
                        ($record->end_time->diffInHours() < 24 ? 'warning' : 'success')
                    )
                    ->weight(fn (Auction $record) => $record->end_time->diffInHours() < 2 ? FontWeight::Bold : FontWeight::Medium
                    ),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn (Auction $record): string => AuctionResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No active auctions')
            ->emptyStateDescription('When auctions go live, they will appear here.')
            ->emptyStateIcon('heroicon-m-rectangle-stack')
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10]);
    }
}
