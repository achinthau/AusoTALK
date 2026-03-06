<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

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
