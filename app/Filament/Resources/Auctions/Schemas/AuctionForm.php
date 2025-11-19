<?php

namespace App\Filament\Resources\Auctions\Schemas;

use App\Enums\AuctionCondition;
use App\Enums\AuctionStatus;
use App\Enums\AuctionType;
use App\Models\Auction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AuctionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Info')
                    ->schema([
                        Select::make('artist_id')
                            ->label('Artist')
                            ->relationship('artist', 'name', modifyQueryUsing: fn (Builder $query): Builder => $query->role('artist'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),
                        MarkdownEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('artwork_images')
                            ->label('Images')
                            ->collection('artwork_images')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->panelLayout('grid')
                            ->maxFiles(5)
                            ->required(fn (Get $get) => blank($get('id')))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Grid::make()
                    ->columns(2)
                    ->schema([
                        Section::make('Auction Settings')
                            ->schema([
                                Radio::make('type')
                                    ->label('Type')
                                    ->options(AuctionType::class)
                                    ->inline()
                                    ->required(),
                                Select::make('status')
                                    ->label('Status')
                                    ->default(AuctionStatus::DRAFT->value)
                                    // ->disabled(fn (string $operation): bool => $operation === 'create')
                                    ->options(function ($operation, Get $get): array {
                                        $options = [AuctionStatus::DRAFT->value => 'DRAFT'];

                                        $user = Auth::user();
                                        if ($operation === 'edit' && $user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                                            $options[AuctionStatus::PUBLISHED->value] = 'PUBLISHED';
                                            
                                            // Only allow LIVE status for OPEN auctions
                                            $type = $get('type');
                                            if ($type !== AuctionType::CLOSED->value) {
                                                $options[AuctionStatus::LIVE->value] = 'LIVE';
                                            }
                                        }

                                        return $options;
                                    })
                                    ->reactive()
                                    ->required(),
                                TextInput::make('starting_price')
                                    ->label('Starting Price')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->rules(fn (Get $get) => filled($get('reserve_price')) ? ['lte:reserve_price'] : []),
                                TextInput::make('reserve_price')
                                    ->label('Reserve Price')
                                    ->numeric()
                                    ->nullable(),
                                DateTimePicker::make('start_time')
                                    ->label('Start Time')
                                    ->default(fn (): string => now()->addHours(3)->toDateTimeString())
                                    ->rules(['after:2 hours']),
                                DateTimePicker::make('end_time')
                                    ->label('End Time')
                                    ->default(fn (): string => now()->addDay()->toDateTimeString())
                                    ->rules(['after:start_time']),
                            ])
                            ->columns(1),

                        Section::make('Metadata')
                            ->schema([
                                TextInput::make('year_created')
                                    ->label('Year Created')
                                    ->maxLength(10),
                                TextInput::make('dimensions')
                                    ->label('Dimensions')
                                    ->placeholder('40 × 60 cm')
                                    ->maxLength(50),
                                Select::make('province')
                                    ->label('Province')
                                    ->options(fn (): array => Auction::query()
                                        ->select('province')
                                        ->whereNotNull('province')
                                        ->distinct()
                                        ->orderBy('province')
                                        ->pluck('province', 'province')
                                        ->all())
                                    ->searchable()
                                    ->preload(),
                                Select::make('condition')
                                    ->label('Condition')
                                    ->options(AuctionCondition::class),
                                Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}
