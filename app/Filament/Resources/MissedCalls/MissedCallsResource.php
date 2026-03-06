<?php

namespace App\Filament\Resources\MissedCalls;

use App\Filament\Resources\MissedCalls\Pages\ListMissedCalls;
use App\Filament\Resources\MissedCalls\Schemas\MissedCallsForm;
use App\Filament\Resources\MissedCalls\Tables\MissedCallsTable;
use App\Models\AbandonedNew;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MissedCallsResource extends Resource
{
    protected static ?string $model = AbandonedNew::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoneXMark;

    protected static ?string $navigationLabel = 'Missed Calls Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    public static function form(Schema $schema): Schema
    {
        return MissedCallsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MissedCallsTable::configure($table);
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
            'index' => ListMissedCalls::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Missed Calls';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Missed Calls';
    }
}
