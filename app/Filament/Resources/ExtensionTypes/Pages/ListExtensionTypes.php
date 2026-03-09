<?php

namespace App\Filament\Resources\ExtensionTypes\Pages;

use App\Filament\Resources\ExtensionTypes\ExtensionTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExtensionTypes extends ListRecords
{
    protected static string $resource = ExtensionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal(),
        ];
    }
}
