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

class EndingSoonTable extends TableWidget
{
    protected static ?string $heading = 'Auctions Ending Soon';

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Auction::query()
                    ->where('status', 'LIVE')
                    ->where('end_time', '>', now())
                    ->where('end_time', '<=', now()->addDay())
                    ->with(['artist', 'category'])
                    ->orderBy('end_time', 'asc')
            )
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('')
                    ->getStateUsing(fn (Auction $record): ?string => $record->thumbnail_url)
                    ->width(50)
                    ->height(50)
                    ->circular(),

                TextColumn::make('title')
                    ->label('Auction')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn (Auction $record): string => $record->title),

                TextColumn::make('artist.name')
                    ->label('Artist')
                    ->searchable()
                    ->limit(15),

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
                    ->label('Time Left')
                    ->formatStateUsing(function (Auction $record): string {
                        $diff = $record->end_time->diffForHumans(null, true);
                        $hours = $record->end_time->diffInHours();

                        if ($hours < 1) {
                            return '⚠️ '.$diff;
                        } elseif ($hours < 2) {
                            return '🔥 '.$diff;
                        } else {
                            return $diff;
                        }
                    })
                    ->color(fn (Auction $record) => $record->end_time->diffInHours() < 1 ? 'danger' :
                        ($record->end_time->diffInHours() < 2 ? 'warning' : 'info')
                    )
                    ->weight(fn (Auction $record) => $record->end_time->diffInHours() < 2 ? FontWeight::Bold : FontWeight::Medium
                    )
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn (Auction $record): string => AuctionResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),

                Action::make('close_now')
                    ->label('Close Now')
                    ->icon('heroicon-m-stop-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Close Auction Now')
                    ->modalDescription('Are you sure you want to close this auction immediately?')
                    ->action(function (Auction $record) {
                        $record->update([
                            'status' => 'ENDED',
                            'end_time' => now(),
                        ]);
                    })
                    ->successNotificationTitle('Auction closed successfully'),
            ])
            ->emptyStateHeading('No auctions ending soon')
            ->emptyStateDescription('Auctions ending in the next 24 hours will appear here.')
            ->emptyStateIcon('heroicon-m-clock')
            ->defaultPaginationPageOption(10)
            ->paginated([5, 10, 15]);
    }
}
