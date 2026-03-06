<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure company_id is included when loading the form
        $data['company_id'] = $this->record->company_id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If company user (logged in user has company_id), force their company_id
        if (auth()->user()?->company_id) {
            $data['company_id'] = auth()->user()->company_id;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
