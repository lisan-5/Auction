<?php

namespace App\Filament\Resources\Notifications;

use App\Filament\Resources\Notifications\Pages\ListNotifications;
use App\Filament\Resources\Notifications\Tables\NotificationsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use UnitEnum;

class NotificationResource extends Resource
{
    protected static ?string $model = DatabaseNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bell;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $modelLabel = 'Notification';

    protected static ?string $pluralModelLabel = 'Notifications';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['data', 'type'];
    }

    public static function getNavigationBadge(): ?string
    {
        return DatabaseNotification::whereNull('read_at')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $unreadCount = DatabaseNotification::whereNull('read_at')->count();
        return $unreadCount > 10 ? 'danger' : ($unreadCount > 0 ? 'warning' : 'success');
    }

    public static function table(Table $table): Table
    {
        return NotificationsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotifications::route('/'),
        ];
    }
}
