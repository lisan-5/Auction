<?php

namespace App\Filament\Resources\Auctions\RelationManagers;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\Bid;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BidsRelationManager extends RelationManager
{
    protected static string $relationship = 'bids';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_winner')
                    ->label('Winner')
                    ->boolean()
                    ->getStateUsing(function (Bid $record): bool {
                        $auction = $record->auction;

                        return $auction->winner_bid_id === $record->id;
                    })
                    ->sortable(false),
                TextColumn::make('user.name')
                    ->label('Bidder')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bid_amount')
                    ->label('Amount')
                    ->money('ETB')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Time')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user')
                    ->label('Bidder')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('amount_range')
                    ->schema([
                        TextInput::make('min_amount')
                            ->label('Minimum Amount')
                            ->numeric(),
                        TextInput::make('max_amount')
                            ->label('Maximum Amount')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('bid_amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('bid_amount', '<=', $amount),
                            );
                    }),
            ])
            ->defaultSort('bid_amount', 'desc')
            ->recordActions([
                Action::make('promoteToWinner')
                    ->label('Promote to winner')
                    ->requiresConfirmation()
                    ->visible(function (Bid $record): bool {
                        $owner = $this->getOwnerRecord();
                        $status = $owner->status instanceof AuctionStatus ? $owner->status->value : $owner->status;

                        return $status === AuctionStatus::ENDED->value && $owner->winner_bid_id === null;
                    })
                    ->action(function (Bid $record): void {
                        $owner = $this->getOwnerRecord();
                        $owner->winner_bid_id = $record->id;
                        $owner->save();
                    }),

                Action::make('removeWinner')
                    ->label('Remove as winner')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(function (Bid $record): bool {
                        $owner = $this->getOwnerRecord();
                        $status = $owner->status instanceof AuctionStatus ? $owner->status->value : $owner->status;

                        return $status === AuctionStatus::ENDED->value && $owner->winner_bid_id === $record->id;
                    })
                    ->action(function (Bid $record): void {
                        $owner = $this->getOwnerRecord();
                        $owner->winner_bid_id = null;
                        $owner->save();
                    }),
            ]);
    }
}
