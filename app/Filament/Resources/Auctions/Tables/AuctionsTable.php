<?php

namespace App\Filament\Resources\Auctions\Tables;

use App\Enums\AuctionCondition;
use App\Enums\AuctionStatus;
use App\Enums\AuctionType;
use App\Filament\Resources\Auctions\AuctionResource;
use App\Models\Auction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AuctionsTable
{
    public static function configure(Table $table): Table
    {
        $table = $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->with(['artist', 'category'])
                    ->orderByRaw('CASE WHEN end_time IS NULL OR end_time < ? THEN 1 ELSE 0 END', [now()]);
            })
            ->defaultSort('end_time', 'asc');

        return $table
            ->columns(self::columns())
            ->filters(self::filters())
            ->filtersFormColumns(2)
            ->filtersFormSchema(fn (array $filters): array => self::filtersForm($filters))
            ->recordActions(self::recordActions())
            ->toolbarActions(self::toolbarActions());
    }

    private static function columns(): array
    {
        return [
            TextColumn::make('title')
                ->label('Title')
                ->url(fn (Auction $record): string => AuctionResource::getUrl('view', ['record' => $record]))
                ->openUrlInNewTab()
                ->searchable(),
            TextColumn::make('artist.name')
                ->label('Artist')
                ->searchable()
                ->sortable(),
            TextColumn::make('type')
                ->label('Type')
                ->badge()
                ->sortable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable(),
            TextColumn::make('current_price')
                ->label('Current Price')
                ->sortable()
                ->formatStateUsing(function ($state): string {
                    if ($state === null) {
                        return '—';
                    }

                    return number_format((float) $state, 2);
                }),
            TextColumn::make('end_time')
                ->label('End Time')
                ->since()
                ->sortable()
                ->badge()
                ->color(function (Auction $record): string {
                    $seconds = (int) ($record->ends_in_seconds ?? 0);
                    if ($seconds <= 0) {
                        return 'gray';
                    }

                    return $seconds < (30 * 60) ? 'warning' : 'success';
                }),
            TextColumn::make('category.name')
                ->label('Category')
                ->badge()
                ->color('gray'),
        ];
    }

    private static function filters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Status')
                ->multiple()
                ->options(AuctionStatus::class),
            Filter::make('type')
                ->label('Type')
                ->schema([
                    Radio::make('value')
                        ->label('Type')
                        ->options([
                            'ALL' => 'All',
                            AuctionType::OPEN->value => 'Open',
                            AuctionType::CLOSED->value => 'Closed',
                        ])
                        ->inline(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $type = $data['value'] ?? null;

                    return in_array($type, [AuctionType::OPEN->value, AuctionType::CLOSED->value], true)
                        ? $query->where('type', $type)
                        : $query;
                }),
            SelectFilter::make('artist')
                ->label('Artist')
                ->relationship('artist', 'name')
                ->searchable()
                ->preload(),
            SelectFilter::make('category')
                ->label('Category')
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),
            SelectFilter::make('province')
                ->label('Province')
                ->options(fn (): array => Auction::query()
                    ->select('province')
                    ->whereNotNull('province')
                    ->distinct()
                    ->orderBy('province')
                    ->pluck('province', 'province')
                    ->all()),
            SelectFilter::make('condition')
                ->label('Condition')
                ->options(AuctionCondition::class),
            Filter::make('ending_soon')
                ->label('Ending in < 1 h')
                ->toggle()
                ->query(function (Builder $query): Builder {
                    return $query
                        ->whereNotNull('end_time')
                        ->whereBetween('end_time', [now(), now()->addHour()]);
                }),
        ];
    }

    private static function filtersForm(array $filters): array
    {
        return [
            Section::make('Core')
                ->schema([
                    $filters['status'],
                    $filters['type'],
                    $filters['artist'],
                    $filters['category'],
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('More')
                ->collapsible()
                ->collapsed()
                ->schema([
                    $filters['province'],
                    $filters['condition'],
                    $filters['ending_soon'],
                ])
                ->columns(3)
                ->columnSpanFull(),
        ];
    }

    private static function recordActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make()
                ->visible(fn (Auction $record): bool => $record->status !== AuctionStatus::ENDED),
            Action::make('close_now')
                ->label('Close Now')
                ->requiresConfirmation()
                ->visible(fn (Auction $record): bool => $record->status !== AuctionStatus::ENDED)
                ->action(function (Auction $record): void {
                    $record->update([
                        'status' => AuctionStatus::ENDED,
                        'end_time' => now(),
                    ]);
                }),
            DeleteAction::make()
                ->visible(fn (Auction $record): bool => $record->status === AuctionStatus::DRAFT && ! $record->bids()->withoutTrashed()->exists()),
        ];
    }

    private static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                BulkAction::make('publish')
                    ->label('Publish')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $ids = $records->modelKeys();
                        Auction::query()->whereKey($ids)->update([
                            'status' => AuctionStatus::PUBLISHED,
                        ]);
                    }),
                BulkAction::make('close_now_bulk')
                    ->label('Close Now')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $ids = $records->modelKeys();
                        Auction::query()
                            ->whereIn('id', $ids)
                            ->where('status', '!=', AuctionStatus::ENDED)
                            ->update([
                                'status' => AuctionStatus::ENDED,
                                'end_time' => now(),
                            ]);
                    }),
                BulkAction::make('export_bids_csv')
                    ->label('Export CSV of bids')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $csv = fopen('php://temp', 'r+');
                        fputcsv($csv, ['auction_id', 'auction_title', 'bid_id', 'bid_amount', 'bidder_id', 'bid_time']);
                        $records->load('bids');
                        foreach ($records as $auction) {
                            foreach ($auction->bids as $bid) {
                                fputcsv($csv, [
                                    $auction->id,
                                    $auction->title,
                                    $bid->id,
                                    $bid->bid_amount,
                                    $bid->user_id,
                                    optional($bid->created_at)?->toIso8601String(),
                                ]);
                            }
                        }
                        rewind($csv);
                        $content = stream_get_contents($csv);
                        fclose($csv);

                        return response()->streamDownload(function () use ($content): void {
                            echo $content;
                        }, 'bids.csv', ['Content-Type' => 'text/csv']);
                    }),
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
