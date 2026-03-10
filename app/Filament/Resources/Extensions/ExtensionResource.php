<?php

namespace App\Filament\Resources\Extensions;

use App\Filament\Resources\Extensions\Pages\ListExtensions;
use App\Filament\Resources\Extensions\Schemas\ExtensionForm;
use App\Filament\Resources\Extensions\Tables\ExtensionsTable;
use App\Models\Extension;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ExtensionResource extends Resource
{
    protected static ?string $model = Extension::class;

    // protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPhone;

    public static function getNavigationIcon(): HtmlString
    {
        return new HtmlString('<svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 4.842C15.976 4.337 14.146 4 12 4c-2.145 0-3.976.337-5.5.842m11 0c3.021 1 4.835 2.66 5.5 3.658L20.5 11l-3-2V4.842zm-11 0c-3.021 1-4.835 2.66-5.5 3.658L3.5 11l3-2V4.842zM10 7v3m0 0-5.414 5.414A2 2 0 0 0 4 16.828V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.172a2 2 0 0 0-.586-1.414L14 10m-4 0h4m0 0V7"></path><circle cx="12" cy="15" r="2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></circle></svg>');
    }
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
        ];
    }
}
