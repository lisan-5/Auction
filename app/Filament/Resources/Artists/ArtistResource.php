<?php

namespace App\Filament\Resources\Artists;

use App\Filament\Resources\Artists\Pages\ListArtists;
use App\Filament\Resources\Artists\Tables\ArtistsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ArtistResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PaintBrush;

    protected static string|UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Artists';

    protected static ?string $modelLabel = 'Artist';

    protected static ?string $pluralModelLabel = 'Artists';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role('artist');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getNavigationBadge(): ?string
    {
        return User::role('artist')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return User::role('artist')->count() > 20 ? 'warning' : 'success';
    }

    public static function table(Table $table): Table
    {
        return ArtistsTable::configure($table);
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
            'index' => ListArtists::route('/'),
        ];
    }
}
