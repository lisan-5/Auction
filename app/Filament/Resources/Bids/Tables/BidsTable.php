<?php

namespace App\Filament\Resources\Bids\Tables;

use App\Filament\Resources\Auctions\AuctionResource;
use App\Filament\Resources\Bids\BidResource;
use App\Models\Bid;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BidsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['auction', 'user']))
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->prefix('#'),
                TextColumn::make('auction.title')
                    ->label('Auction')
                    ->url(fn (Bid $record): string => AuctionResource::getUrl('view', ['record' => $record->auction]))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('auction.status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : (string) $state)
                    ->sortable(),
                ImageColumn::make('avatar')
                    ->label('')
                    ->getStateUsing(fn (Bid $record): string => 'https://www.gravatar.com/avatar/'.md5(strtolower(trim((string) ($record->user->email ?? '')))).'?s=40&d=mp')
                    ->circular(),
                TextColumn::make('user.name')
                    ->label('Bidder')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bid_amount')
                    ->label('Amount')
                    ->money('ETB')
                    ->weight(FontWeight::Bold)
                    ->sortable()
                    ->url(fn (Bid $record): string => BidResource::getUrl('view', ['record' => $record]))
                    ->color('primary'),
                TextColumn::make('is_visible')
                    ->label('Visibility')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Visible' : 'Hidden')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->dateTimeTooltip('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('max_auto_bid')
                    ->label('Max Auto Bid')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, '.', ',').' ETB' : '—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('auction')
                    ->label('Auction')
                    ->relationship('auction', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user')
                    ->label('Bidder')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->boolean()
                    ->trueLabel('Visible')
                    ->falseLabel('Hidden'),
                Filter::make('created_between')
                    ->label('Created Between')
                    ->schema([
                        DatePicker::make('created_from')->label('From'),
                        DatePicker::make('created_until')->label('Until'),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (! empty($data['created_from'])) {
                            $indicators[] = 'From '.($data['created_from']);
                        }
                        if (! empty($data['created_until'])) {
                            $indicators[] = 'Until '.($data['created_until']);
                        }

                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->filtersFormColumns(2)
            ->filtersFormSchema(fn ($filters): array => [
                Section::make('General')
                    ->schema(array_values(array_filter([
                        $filters['auction'] ?? null,
                        $filters['user'] ?? null,
                    ])))
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Visibility & Date')
                    ->schema(array_values(array_filter([
                        $filters['is_visible'] ?? null,
                        $filters['created_between'] ?? null,
                    ])))
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('bids-filters-visibility-date'),
            ])
            ->recordActions([
                ViewAction::make(),
                RestoreAction::make()
                    ->visible(fn (Bid $record): bool => method_exists($record, 'trashed') && $record->trashed()),
                DeleteAction::make()
                    ->label('Void')
                    ->hidden(fn (Bid $record): bool => (method_exists($record, 'trashed') && $record->trashed()) || (string) ($record->auction->status instanceof \BackedEnum ? $record->auction->status->value : $record->auction->status) === 'ENDED'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make()
                        ->visible(fn () => request()->get('activeTab') === 'with_deleted'),
                    DeleteBulkAction::make()
                        ->label('Void Selected'),
                ]),
            ]);
    }
}
