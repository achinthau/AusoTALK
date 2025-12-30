<?php

namespace App\Filament\Resources\ExtensionTypes\Pages;

use App\Filament\Resources\ExtensionTypes\ExtensionTypeResource;
use Filament\Resources\Pages\EditRecord;

class EditExtensionType extends EditRecord
{
    protected static string $resource = ExtensionTypeResource::class;
}
