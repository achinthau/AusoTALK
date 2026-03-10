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
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ExtensionTypeResource extends Resource
{
    protected static ?string $model = ExtensionType::class;

    public static function getNavigationIcon(): HtmlString
    {
        return new HtmlString('<svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.5 1h2v1H2v12h1.5v1h-2l-.5-.5v-13l.5-.5zm6 6h-2L5 6.5v-2l.5-.5h2l.5.5v2l-.5.5zM6 6h1V5H6v1zm7.5 1h-3l-.5-.5v-3l.5-.5h3l.5.5v3l-.5.5zM11 6h2V4h-2v2zm-3.5 6h-2l-.5-.5v-2l.5-.5h2l.5.5v2l-.5.5zM6 11h1v-1H6v1zm7.5 2h-3l-.5-.5v-3l.5-.5h3l.5.5v3l-.5.5zM11 12h2v-2h-2v2zm-1-2H8v1h2v-1zm0-5H8v1h2V5z"></path></svg>');
    }
    protected static ?string $navigationLabel = 'Extension Types';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 5;

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
        return auth()->user()?->hasRole('super_admin') || auth()->user()?->hasRole('company_admin') ?? false;
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
