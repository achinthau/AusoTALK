<?php

namespace App\Filament\Resources\Extensions;

use App\Filament\Resources\Extensions\Pages\CreateExtension;
use App\Filament\Resources\Extensions\Pages\EditExtension;
use App\Filament\Resources\Extensions\Pages\ListExtensions;
use App\Filament\Resources\Extensions\Schemas\ExtensionForm;
use App\Filament\Resources\Extensions\Tables\ExtensionsTable;
use App\Models\Extension;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExtensionResource extends Resource
{
    protected static ?string $model = Extension::class;

    protected static \BackedEnum|string|null $navigationIcon =Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Extensions';

    protected static string|\UnitEnum|null $navigationGroup = 'PBX';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ExtensionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExtensionsTable::configure($table)
            ->modifyQueryUsing(fn ($query) => self::scopeByCompany($query));
    }

    protected static function scopeByCompany($query)
    {
        $user = auth()->user();
        // If user has a company, show only extensions from that company
        if ($user && $user->company_id) {
            return $query->where('company_id', $user->company_id);
        }
        // Super admin sees all extensions
        return $query;
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
            'index' => ListExtensions::route('/'),
            'create' => CreateExtension::route('/create'),
            'edit' => EditExtension::route('/{record}/edit'),
        ];
    }
}
