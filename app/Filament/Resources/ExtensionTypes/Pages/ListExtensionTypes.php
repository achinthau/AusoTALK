<?php

namespace App\Filament\Resources\ExtensionTypes\Pages;

use App\Filament\Resources\ExtensionTypes\ExtensionTypeResource;
use Filament\Resources\Pages\ListRecords;

class ListExtensionTypes extends ListRecords
{
    protected static string $resource = ExtensionTypeResource::class;
}
