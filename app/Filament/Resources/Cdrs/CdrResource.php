<?php

namespace App\Filament\Resources\Cdrs;

use App\Filament\Resources\Cdrs\Pages\ListCdrs;
use App\Filament\Resources\Cdrs\Schemas\CdrForm;
use App\Filament\Resources\Cdrs\Tables\CdrsTable;
use App\Models\Cdr;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CdrResource extends Resource
{
    protected static ?string $model = Cdr::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoneArrowDownLeft;

    protected static ?string $navigationLabel = 'CDR Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    public static function form(Schema $schema): Schema
    {
        return CdrForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CdrsTable::configure($table);
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
            'index' => ListCdrs::route('/'),
        ];
    }
}
