<?php

namespace App\Filament\Resources\Notifications\Tables;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->icon('heroicon-m-bell')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('notifiable.name')
                    ->label('Recipient')
                    ->url(fn (DatabaseNotification $record): ?string => 
                        $record->notifiable_type === 'App\\Models\\User' 
                            ? UserResource::getUrl('edit', ['record' => $record->notifiable])
                            : null
                    )
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-user')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('data')
                    ->label('Message')
                    ->formatStateUsing(function (array $state): string {
                        // Extract meaningful message from notification data
                        if (isset($state['message'])) {
                            return $state['message'];
                        } elseif (isset($state['title'])) {
                            return $state['title'];
                        } elseif (isset($state['body'])) {
                            return $state['body'];
                        } else {
                            return 'Notification data: ' . collect($state)->take(3)->map(fn ($v, $k) => "$k: $v")->implode(', ');
                        }
                    })
                    ->limit(50)
                    ->tooltip(function (array $state): string {
                        return json_encode($state, JSON_PRETTY_PRINT);
                    })
                    ->searchable(),
                    
                IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn (DatabaseNotification $record): bool => $record->read_at !== null)
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->icon('heroicon-m-calendar'),
                    
                TextColumn::make('read_at')
                    ->label('Read At')
                    ->dateTime()
                    ->since()
                    ->placeholder('Unread')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('read_status')
                    ->label('Read Status')
                    ->placeholder('All notifications')
                    ->trueLabel('Read')
                    ->falseLabel('Unread')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('read_at'),
                        false: fn ($query) => $query->whereNull('read_at'),
                    ),
                    
                SelectFilter::make('type')
                    ->label('Notification Type')
                    ->options(function (): array {
                        return DatabaseNotification::distinct()
                            ->pluck('type')
                            ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                            ->toArray();
                    })
                    ->searchable(),
                    
                SelectFilter::make('notifiable_type')
                    ->label('Recipient Type')
                    ->options([
                        'App\\Models\\User' => 'User',
                        // Add other notifiable types if needed
                    ]),
            ])
            ->recordActions([
                Action::make('mark_read')
                    ->label('Mark as Read')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (DatabaseNotification $record): bool => $record->read_at === null)
                    ->action(fn (DatabaseNotification $record) => $record->markAsRead()),
                    
                Action::make('mark_unread')
                    ->label('Mark as Unread')
                    ->icon('heroicon-m-clock')
                    ->color('warning')
                    ->visible(fn (DatabaseNotification $record): bool => $record->read_at !== null)
                    ->action(fn (DatabaseNotification $record) => $record->update(['read_at' => null])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('mark_all_read')
                        ->label('Mark All as Read')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function () {
                            DatabaseNotification::whereNull('read_at')->update(['read_at' => now()]);
                        }),
                ]),
            ])
            ->emptyStateHeading('No notifications yet')
            ->emptyStateDescription('Notifications will appear here when they are sent to users.')
            ->emptyStateIcon('heroicon-m-bell');
    }
}
