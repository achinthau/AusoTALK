<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    private ?string $roleToAssign = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load the user's role
        $data['roles'] = $this->record->roles->first()?->name;

        // Ensure all user attributes are present
        $data['name'] = $this->record->name;
        $data['email'] = $this->record->email;
        $data['phone'] = $this->record->phone;
        $data['nic'] = $this->record->nic;
        $data['gender'] = $this->record->gender;
        $data['address'] = $this->record->address;
        $data['company_id'] = $this->record->company_id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Store role before unsetting so it can be used in afterSave
        $this->roleToAssign = $data['roles'] ?? null;

        // If company user (logged in user has company_id), force their company_id
        if (auth()->user()?->company_id) {
            $data['company_id'] = auth()->user()->company_id;
        }

        // Handle password - remove if empty (leave unchanged), otherwise let Hashed cast handle encryption
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Always remove password_confirmation - it's not a database field
        unset($data['password_confirmation']);

        // Remove roles from data - it's not a model attribute, handled in afterSave
        unset($data['roles']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Assign the stored role
        if ($this->roleToAssign) {
            $this->record->syncRoles([$this->roleToAssign]);
        }
    }
}
