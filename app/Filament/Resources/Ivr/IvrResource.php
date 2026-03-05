<?php

namespace App\Filament\Resources\Ivr;

use App\Filament\Resources\Ivr\Pages\AnalyticsIvr;
use App\Filament\Resources\Ivr\Pages\ListIvr;
use App\Filament\Resources\Ivr\Schemas\IvrForm;
use App\Filament\Resources\Ivr\Tables\IvrTable;
use App\Models\AuIvrCall;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IvrResource extends Resource
{
    protected static ?string $model = AuIvrCall::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $navigationLabel = 'IVR Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    public static function form(Schema $schema): Schema
    {
        return IvrForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IvrTable::configure($table);
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
            'index' => ListIvr::route('/'),
            'analytics' => AnalyticsIvr::route('/analytics'),
        ];
    }
}
