<?php

namespace App\Filament\Resources\Bids\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BidForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bid Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('auction_id')
                                    ->label('Auction')
                                    ->relationship('auction', 'title')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('user_id')
                                    ->label('Bidder')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('bid_amount')
                                    ->label('Bid Amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('ETB')
                                    ->step(0.01)
                                    ->minValue(0),

                                TextInput::make('max_auto_bid')
                                    ->label('Max Auto Bid')
                                    ->numeric()
                                    ->prefix('ETB')
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->helperText('Optional: Maximum amount for automatic bidding'),
                            ]),

                        Toggle::make('is_visible')
                            ->label('Visible to Public')
                            ->default(true)
                            ->helperText('Whether this bid should be visible to other users'),

                        Textarea::make('note')
                            ->label('Internal Note')
                            ->rows(3)
                            ->placeholder('Any internal notes about this bid...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
