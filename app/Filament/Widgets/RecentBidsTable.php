<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Auctions\AuctionResource;
use App\Filament\Resources\Bids\BidResource;
use App\Models\Bid;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentBidsTable extends TableWidget
{
    protected static ?string $heading = 'Recent Bids Activity';

    protected ?string $pollingInterval = '15s';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Bid::query()
                    ->with(['auction', 'user'])
                    ->latest()
                    ->limit(50)
            )
            ->columns([
                TextColumn::make('id')
                    ->label('Bid #')
                    ->prefix('#')
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('auction.title')
                    ->label('Auction')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn (Bid $record): string => $record->auction->title),

                TextColumn::make('user.name')
                    ->label('Bidder')
                    ->searchable()
                    ->limit(15),

                TextColumn::make('bid_amount')
                    ->label('Amount')
                    ->formatStateUsing(fn (string $state): string => 'ETB '.number_format((float) $state, 2))
                    ->weight(FontWeight::Medium)
                    ->color('success')
                    ->sortable(),

                TextColumn::make('max_auto_bid')
                    ->label('Max Auto')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'ETB '.number_format((float) $state, 2) : '—'
                    )
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean()
                    ->trueIcon('heroicon-m-eye')
                    ->falseIcon('heroicon-m-eye-slash')
                    ->trueColor('success')
                    ->falseColor('warning'),

                TextColumn::make('created_at')
                    ->label('Time')
                    ->since()
                    ->sortable()
                    ->color('gray'),
            ])
            ->recordActions([
                Action::make('view_bid')
                    ->label('View Bid')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn (Bid $record): string => BidResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),

                Action::make('view_auction')
                    ->label('View Auction')
                    ->icon('heroicon-m-rectangle-stack')
                    ->color('primary')
                    ->url(fn (Bid $record): string => AuctionResource::getUrl('view', ['record' => $record->auction]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No recent bids')
            ->emptyStateDescription('Recent bidding activity will appear here.')
            ->emptyStateIcon('heroicon-m-hand-raised')
            ->defaultPaginationPageOption(10)
            ->paginated([5, 10, 15, 25]);
    }
}
