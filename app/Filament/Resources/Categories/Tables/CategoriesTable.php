<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-m-squares-2x2')
                    ->color('primary'),

                TextColumn::make('slug')
                    ->label('URL Slug')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-link')
                    ->color('gray')
                    ->fontFamily('mono'),

                TextColumn::make('auctions_count')
                    ->label('Auctions')
                    ->counts('auctions')
                    ->sortable()
                    ->icon('heroicon-m-rectangle-stack')
                    ->color('info')
                    ->alignCenter(),



                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->icon('heroicon-m-pencil-square')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                // Add filters if needed in the future
            ])
            ->recordActions([
                EditAction::make()
                    ->color('warning'),
                DeleteAction::make()
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete these categories? This action cannot be undone.'),
                ]),
            ])
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Create your first category to start organizing artworks.')
            ->emptyStateIcon('heroicon-m-squares-2x2');
    }
}
