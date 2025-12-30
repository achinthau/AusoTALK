<?php

namespace App\Filament\Resources\ExtensionTypes\Pages;

use App\Filament\Resources\ExtensionTypes\ExtensionTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExtensionType extends CreateRecord
{
    protected static string $resource = ExtensionTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
