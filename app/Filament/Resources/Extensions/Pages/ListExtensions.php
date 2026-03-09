<?php

namespace App\Filament\Resources\Extensions\Pages;

use App\Filament\Resources\Extensions\Actions\CreateExtensionAction;
use App\Filament\Resources\Extensions\ExtensionResource;
use Filament\Resources\Pages\ListRecords;

class ListExtensions extends ListRecords
{
    protected static string $resource = ExtensionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateExtensionAction::make(),
        ];
    }
}
