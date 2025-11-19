<?php

namespace App\Filament\Resources\Bids;

use App\Filament\Resources\Auctions\AuctionResource;
use App\Filament\Resources\Bids\Pages\ListBids;
use App\Filament\Resources\Bids\Pages\ViewBid;
use App\Filament\Resources\Bids\Schemas\BidForm;
use App\Filament\Resources\Bids\Tables\BidsTable;
use App\Filament\Resources\Users\UserResource;
use App\Models\Bid;
use BackedEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BidResource extends Resource
{
    protected static ?string $model = Bid::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::HandRaised;

    protected static string|UnitEnum|null $navigationGroup = 'Auction Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'auction.title', 'user.name', 'user.email'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Auction' => $record->auction?->title ?? '—',
            'Bidder' => $record->user?->name ?? '—',
            'Amount' => 'ETB '.number_format($record->bid_amount, 2),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['auction', 'user']);
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return "Bid #{$record->id}";
    }

    public static function form(Schema $schema): Schema
    {
        return BidForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BidsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            SchemaGrid::make()
                ->columns(12)
                ->schema([
                    SchemaSection::make('Auction & Bidder')
                        ->schema([
                            TextEntry::make('auction.title')
                                ->label('Auction')
                                ->url(fn (Bid $record): string => AuctionResource::getUrl('view', ['record' => $record->auction]))
                                ->weight(FontWeight::Bold)
                                ->icon('heroicon-m-rectangle-stack')
                                ->size('lg'),
                            TextEntry::make('user.name')
                                ->label('Bidder')
                                ->url(fn (Bid $record): string => UserResource::getUrl('edit', ['record' => $record->user]))
                                ->openUrlInNewTab()
                                ->weight(FontWeight::Medium)
                                ->icon('heroicon-m-user'),
                            TextEntry::make('user.email')
                                ->label('Email')
                                ->icon('heroicon-m-envelope'),
                        ])
                        ->columns(2)
                        ->columnSpan(fn (Bid $record): int => $record->getMedia('attachments')->count() > 0 ? 8 : 12),
                        
                    SchemaSection::make('Bid Details')
                        ->schema([
                            TextEntry::make('bid_amount')
                                ->label('Bid Amount')
                                ->money('ETB')
                                ->weight(FontWeight::Bold)
                                ->size('lg')
                                ->icon('heroicon-m-banknotes')
                                ->color('success'),
                            TextEntry::make('max_auto_bid')
                                ->label('Maximum Auto Bid')
                                ->formatStateUsing(fn ($state) => $state !== null ? 'ETB ' . number_format((float) $state, 2) : 'No auto bidding')
                                ->icon('heroicon-m-arrow-trending-up')
                                ->color(fn ($state) => $state !== null ? 'warning' : 'gray'),
                            TextEntry::make('is_visible')
                                ->label('Visibility Status')
                                ->badge()
                                ->formatStateUsing(fn (bool $state): string => $state ? 'Public' : 'Hidden')
                                ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                                ->icon(fn (bool $state): string => $state ? 'heroicon-m-eye' : 'heroicon-m-eye-slash'),
                            TextEntry::make('created_at')
                                ->label('Bid Placed')
                                ->since()
                                ->dateTimeTooltip()
                                ->icon('heroicon-m-clock'),
                        ])
                        ->columns(2)
                        ->columnSpan(fn (Bid $record): int => $record->getMedia('attachments')->count() > 0 ? 8 : 12),
                        
                    SchemaSection::make('Additional Information')
                        ->schema([
                            ImageEntry::make('bidder_avatar')
                                ->label('Bidder Avatar')
                                ->getStateUsing(fn (Bid $record): string => 'https://www.gravatar.com/avatar/'.md5(strtolower(trim((string) ($record->user->email ?? '')))).'?s=120&d=mp')
                                ->circular()
                                ->size(120),
                            TextEntry::make('note')
                                ->label('Bidder Note')
                                ->prose()
                                ->placeholder('No additional notes provided')
                                ->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->columnSpan(fn (Bid $record): int => $record->getMedia('attachments')->count() > 0 ? 8 : 12)
                        ->visible(fn (Bid $record) => !blank($record->note) || $record->getMedia('attachments')->count() == 0),

                    SchemaSection::make('Attachments')
                        ->schema([
                            TextEntry::make('attachment_link')
                                ->label('Download')
                                ->icon('heroicon-m-arrow-down-tray')
                                ->url(fn (Bid $record): ?string => optional($record->getFirstMedia('attachments'))->getUrl())
                                ->visible(fn (Bid $record) => (bool) $record->getFirstMedia('attachments')),
                            ImageEntry::make('attachment_preview')
                                ->label('Preview')
                                ->getStateUsing(fn (Bid $record): ?string => optional($record->getFirstMedia('attachments'))->getUrl())
                                ->visible(fn (Bid $record) => (bool) optional($record->getFirstMedia('attachments'))->mime_type && str_starts_with(optional($record->getFirstMedia('attachments'))->mime_type, 'image/')),
                            TextEntry::make('attachment_type')
                                ->label('File Type')
                                ->formatStateUsing(fn (Bid $record): ?string => optional($record->getFirstMedia('attachments'))->mime_type)
                                ->visible(fn (Bid $record) => (bool) $record->getFirstMedia('attachments')),
                        ])
                        ->columnSpan(4)
                        ->visible(fn (Bid $record) => $record->getMedia('attachments')->count() > 0),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relations can be added here if needed in the future
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBids::route('/'),
            'view' => ViewBid::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 50 ? 'warning' : 'primary';
    }
}
