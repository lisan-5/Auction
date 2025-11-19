<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Information')
                    ->description('Define the category name and unique identifier for artwork classification.')
                    ->icon('heroicon-m-squares-2x2')
                    ->schema([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null
                            )
                            ->placeholder('e.g., Oil Paintings')
                            ->helperText('The display name for this category'),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignorable: fn ($record) => $record)
                            ->rules(['alpha_dash'])
                            ->placeholder('e.g., oil-paintings')
                            ->helperText('Unique identifier used in URLs (automatically generated from name)'),


                    ])
                    ->columns(2),
            ]);
    }
}
