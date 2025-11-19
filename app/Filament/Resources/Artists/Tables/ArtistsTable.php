<?php

namespace App\Filament\Resources\Artists\Tables;

use App\Filament\Resources\Auctions\AuctionResource;
use App\Filament\Resources\Bids\BidResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ArtistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->getStateUsing(fn (User $record): string => 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($record->email))).'?s=40&d=mp')
                    ->circular(),
                    
                TextColumn::make('name')
                    ->label('Artist Name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-m-user-circle'),
                    
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope')
                    ->color('gray'),
                    
                TextColumn::make('roles')
                    ->label('Roles')
                    ->badge()
                    ->getStateUsing(fn (User $record): array => $record->roles->pluck('name')->toArray())
                    ->separator(','),
                    
                TextColumn::make('auctions_count')
                    ->label('Auctions')
                    ->counts('auctions')
                    ->sortable()
                    ->icon('heroicon-m-rectangle-stack')
                    ->color('info')
                    ->alignCenter(),
                    
                TextColumn::make('bids_count')
                    ->label('Bids')
                    ->counts('bids')
                    ->sortable()
                    ->icon('heroicon-m-hand-raised')
                    ->color('warning')
                    ->alignCenter(),
                    
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                SelectFilter::make('has_auctions')
                    ->label('Has Auctions')
                    ->options([
                        'yes' => 'Has Auctions',
                        'no' => 'No Auctions',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'yes') {
                            return $query->has('auctions');
                        } elseif ($data['value'] === 'no') {
                            return $query->doesntHave('auctions');
                        }
                        return $query;
                    }),
            ])
            ->recordActions([
                Action::make('view_auctions')
                    ->label('Auctions')
                    ->icon('heroicon-m-rectangle-stack')
                    ->color('info')
                    ->url(fn (User $record): string => AuctionResource::getUrl('index') . '?filters[artist][value]=' . $record->id)
                    ->openUrlInNewTab(),
                    
                Action::make('view_bids')
                    ->label('Bids')
                    ->icon('heroicon-m-hand-raised')
                    ->color('warning')
                    ->url(fn (User $record): string => BidResource::getUrl('index') . '?filters[user][value]=' . $record->id)
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete these artists? This action cannot be undone.'),
                ]),
            ])
            ->emptyStateHeading('No artists yet')
            ->emptyStateDescription('Artists will appear here once users are assigned the artist role.')
            ->emptyStateIcon('heroicon-m-paint-brush');
    }
}
