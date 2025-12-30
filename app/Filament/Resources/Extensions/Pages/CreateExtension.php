<?php

namespace App\Filament\Resources\Extensions\Pages;

use App\Filament\Resources\Extensions\ExtensionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExtension extends CreateRecord
{
    protected static string $resource = ExtensionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If company user (logged in user has company_id), force their company_id
        if (auth()->user()?->company_id) {
            $data['company_id'] = auth()->user()->company_id;
        }
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
