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

        // Handle password - only include if it's not empty
        if (empty($data['password'])) {
            unset($data['password']);
            unset($data['password_confirmation']);
        } else {
            // Hash the password if provided
            $data['password'] = bcrypt($data['password']);
            unset($data['password_confirmation']);
        }

        // Remove roles from data if it exists (it's not a model attribute, handled in afterSave)
        if (isset($data['roles'])) {
            unset($data['roles']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Get the role from the form state
        $formState = $this->form->getState();
        if (isset($formState['roles']) && ! empty($formState['roles'])) {
            $this->record->syncRoles([$formState['roles']]);
        }
    }
}
