<?php

namespace App\Filament\Resources\ExtensionTypes;

use App\Filament\Resources\ExtensionTypes\Pages\CreateExtensionType;
use App\Filament\Resources\ExtensionTypes\Pages\EditExtensionType;
use App\Filament\Resources\ExtensionTypes\Pages\ListExtensionTypes;
use App\Filament\Resources\ExtensionTypes\Schemas\ExtensionTypeForm;
use App\Filament\Resources\ExtensionTypes\Tables\ExtensionTypesTable;
use App\Models\ExtensionType;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExtensionTypeResource extends Resource
{
    protected static ?string $model = ExtensionType::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Extension Types';

    protected static string|\UnitEnum|null $navigationGroup = 'PBX';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('super_admin') || auth()->user()?->hasRole('company_admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return ExtensionTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExtensionTypesTable::configure($table);
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
            'index' => ListExtensionTypes::route('/'),
            'create' => CreateExtensionType::route('/create'),
            'edit' => EditExtensionType::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ||  auth()->user()?->hasRole('company_admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
