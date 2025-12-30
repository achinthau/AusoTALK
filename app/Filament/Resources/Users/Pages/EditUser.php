<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->record->roles->first()?->name;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If company user (logged in user has company_id), force their company_id
        if (auth()->user()?->company_id) {
            $data['company_id'] = auth()->user()->company_id;
        }
        
        $role = $data['roles'];
        unset($data['roles']);
        
        return $data;
    }

    protected function afterSave(): void
    {
        $role = $this->form->getState()['roles'];
        $this->record->syncRoles([$role]);
    }
}
