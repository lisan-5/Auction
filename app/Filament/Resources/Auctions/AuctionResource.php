<?php

namespace App\Filament\Resources\Auctions;

use App\Filament\Resources\Auctions\Pages\CreateAuction;
use App\Filament\Resources\Auctions\Pages\EditAuction;
use App\Filament\Resources\Auctions\Pages\ListAuctions;
use App\Filament\Resources\Auctions\Pages\ViewAuction;
use App\Filament\Resources\Auctions\RelationManagers\BidsRelationManager;
use App\Filament\Resources\Auctions\Schemas\AuctionForm;
use App\Filament\Resources\Auctions\Tables\AuctionsTable;
use App\Filament\Resources\Bids\BidResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Auction;
use BackedEnum;
use Filament\Infolists;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Tabs as SchemaTabs;
use Filament\Schemas\Components\Tabs\Tab as SchemaTab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AuctionResource extends Resource
{
    protected static ?string $model = Auction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Auction Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'artist.name', 'category.name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Artist' => $record->artist?->name ?? '—',
            'Status' => $record->status instanceof BackedEnum ? $record->status->value : (string) $record->status,
            'Type' => $record->type instanceof BackedEnum ? $record->type->value : (string) $record->type,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['artist', 'category']);
    }

    public static function form(Schema $schema): Schema
    {
        return AuctionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuctionsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            SchemaTabs::make()
                ->tabs([
                    SchemaTab::make('Overview')
                        ->schema([
                            SchemaGrid::make()
                                ->columns(12)
                                ->schema([
                                    SchemaSection::make('Auction Overview')
                                        ->schema([
                                            TextEntry::make('title')
                                                ->label('Auction Title')
                                                ->weight(FontWeight::Bold)
                                                ->size('lg')
                                                ->icon('heroicon-m-rectangle-stack')
                                                ->color('primary'),
                                            TextEntry::make('artist.name')
                                                ->label('Artist')
                                                ->url(fn (Auction $record): string => UserResource::getUrl('edit', ['record' => $record->artist]))
                                                ->openUrlInNewTab()
                                                ->weight(FontWeight::Medium)
                                                ->icon('heroicon-m-user-circle'),
                                            TextEntry::make('status')
                                                ->label('Status')
                                                ->badge()
                                                ->formatStateUsing(fn ($state) => $state instanceof BackedEnum ? $state->value : (string) $state)
                                                ->icon('heroicon-m-signal'),
                                            TextEntry::make('type')
                                                ->label('Auction Type')
                                                ->badge()
                                                ->formatStateUsing(fn ($state) => $state instanceof BackedEnum ? $state->value : (string) $state)
                                                ->icon('heroicon-m-tag'),
                                        ])
                                        ->columns(2)
                                        ->columnSpan(fn (Auction $record): int => $record->getMedia('artwork_images')->count() > 0 ? 8 : 12),

                                    SchemaSection::make('Financial Details')
                                        ->schema([
                                            TextEntry::make('starting_price')
                                                ->label('Starting Price')
                                                ->money('ETB')
                                                ->weight(FontWeight::Medium)
                                                ->icon('heroicon-m-play')
                                                ->color('info'),
                                            TextEntry::make('reserve_price')
                                                ->label('Reserve Price')
                                                ->money('ETB')
                                                ->visible(fn (Auction $record): bool => $record->reserve_price > 0)
                                                ->icon('heroicon-m-shield-check')
                                                ->color('warning'),
                                            TextEntry::make('current_highest_bid')
                                                ->label('Current Highest Bid')
                                                ->formatStateUsing(fn (Auction $record): string => 'ETB '.number_format($record->currentHighestBid(), 2))
                                                ->weight(FontWeight::Bold)
                                                ->size('lg')
                                                ->icon('heroicon-m-trophy')
                                                ->color('success'),
                                        ])
                                        ->columns(2)
                                        ->columnSpan(fn (Auction $record): int => $record->getMedia('artwork_images')->count() > 0 ? 8 : 12),

                                    SchemaSection::make('Timing Information')
                                        ->schema([
                                            TextEntry::make('start_time')
                                                ->label('Auction Started')
                                                ->since()
                                                ->dateTimeTooltip()
                                                ->icon('heroicon-m-play-circle')
                                                ->color('success'),
                                            TextEntry::make('end_time')
                                                ->label('Auction Ends')
                                                ->since()
                                                ->dateTimeTooltip()
                                                ->icon('heroicon-m-stop-circle')
                                                ->color(fn (Auction $record) => 
                                                    $record->end_time && $record->end_time->isPast() ? 'danger' : 'warning'
                                                ),
                                        ])
                                        ->columns(2)
                                        ->columnSpan(fn (Auction $record): int => $record->getMedia('artwork_images')->count() > 0 ? 8 : 12),

                                    SchemaSection::make('Artwork')
                                        ->schema([
                                            ImageEntry::make('thumbnail')
                                                ->label('Preview')
                                                ->getStateUsing(fn (Auction $record): ?string => $record->thumbnail_url)
                                                ->visible(fn (Auction $record) => (bool) $record->thumbnail_url),
                                            TextEntry::make('gallery_info')
                                                ->label('Images')
                                                ->formatStateUsing(fn (Auction $record): string => (string) $record->getMedia('artwork_images')->count().' file(s)')
                                                ->visible(fn (Auction $record) => $record->getMedia('artwork_images')->count() > 0),
                                        ])
                                        ->columnSpan(4)
                                        ->visible(fn (Auction $record) => $record->getMedia('artwork_images')->count() > 0),
                                ]),
                        ]),

                    SchemaTab::make('Metadata')
                        ->schema([
                            SchemaSection::make('Artwork Details')
                                ->schema([
                                    TextEntry::make('year_created')
                                        ->label('Year Created')
                                        ->formatStateUsing(fn ($state) => $state ?: 'Not specified')
                                        ->icon('heroicon-m-calendar')
                                        ->placeholder('Not specified'),
                                    TextEntry::make('dimensions')
                                        ->label('Dimensions')
                                        ->formatStateUsing(fn ($state) => $state ?: 'Not specified')
                                        ->icon('heroicon-m-square-3-stack-3d')
                                        ->placeholder('Not specified'),
                                    TextEntry::make('condition')
                                        ->label('Artwork Condition')
                                        ->badge()
                                        ->formatStateUsing(fn ($state) => $state instanceof BackedEnum ? $state->value : ((string) $state ?: 'Not specified'))
                                        ->icon('heroicon-m-shield-check'),
                                ])
                                ->columns(3)
                                ->columnSpanFull(),

                            SchemaSection::make('Location & Category')
                                ->schema([
                                    TextEntry::make('province')
                                        ->label('Province/Location')
                                        ->formatStateUsing(fn ($state) => $state ?: 'Not specified')
                                        ->icon('heroicon-m-map-pin')
                                        ->placeholder('Not specified'),
                                    TextEntry::make('category.name')
                                        ->label('Art Category')
                                        ->formatStateUsing(fn ($state) => $state ?: 'Uncategorized')
                                        ->icon('heroicon-m-tag')
                                        ->color('info')
                                        ->badge(),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ]),

                    SchemaTab::make('System')
                        ->schema([
                            SchemaSection::make('Database Information')
                                ->schema([
                                    TextEntry::make('id')
                                        ->label('Auction ID')
                                        ->icon('heroicon-m-hashtag')
                                        ->prefix('#')
                                        ->weight(FontWeight::Bold)
                                        ->color('primary'),
                                    TextEntry::make('artist_id')
                                        ->label('Artist ID')
                                        ->icon('heroicon-m-user')
                                        ->prefix('Artist #'),
                                    TextEntry::make('winner_bid_id')
                                        ->label('Winning Bid')
                                        ->url(fn (Auction $record): ?string => $record->winnerBid ? BidResource::getUrl('view', ['record' => $record->winnerBid]) : null)
                                        ->openUrlInNewTab()
                                        ->formatStateUsing(fn ($state) => $state ? "Bid #{$state}" : 'No winner yet')
                                        ->icon(fn ($state) => $state ? 'heroicon-m-trophy' : 'heroicon-m-minus-circle')
                                        ->color(fn ($state) => $state ? 'success' : 'gray'),
                                ])
                                ->columns(3)
                                ->columnSpanFull(),

                            SchemaSection::make('Timestamps')
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->dateTime()
                                        ->since()
                                        ->icon('heroicon-m-plus-circle')
                                        ->color('info'),
                                    TextEntry::make('updated_at')
                                        ->label('Last Updated')
                                        ->dateTime()
                                        ->since()
                                        ->icon('heroicon-m-pencil-square')
                                        ->color('warning'),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ]),
                ])
                ->persistTabInQueryString()
                ->columnSpanFull(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            BidsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuctions::route('/'),
            'create' => CreateAuction::route('/create'),
            'edit' => EditAuction::route('/{record}/edit'),
            'view' => ViewAuction::route('/{record}'),
        ];
    }
}
