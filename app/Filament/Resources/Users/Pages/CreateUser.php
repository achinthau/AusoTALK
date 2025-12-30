<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    private ?string $roleToAssign = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If company user (logged in user has company_id), force their company_id
        if (auth()->user()?->company_id) {
            $data['company_id'] = auth()->user()->company_id;
        }
        
        // If no company_id, always set role to super_admin
        if (empty($data['company_id'])) {
            $this->roleToAssign = 'super_admin';
        } else {
            // If company is selected, get the role from form data
            $this->roleToAssign = $data['roles'] ?? null;
        }
        
        \Log::info('User creation:', [
            'email' => $data['email'],
            'company_id' => $data['company_id'] ?? 'null',
            'roleToAssign' => $this->roleToAssign,
        ]);
        
        // Remove roles from data as we'll assign it in afterCreate
        unset($data['roles']);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        \Log::info('After create - assigning role:', [
            'user_email' => $this->record->email,
            'roleToAssign' => $this->roleToAssign,
        ]);
        
        if ($this->roleToAssign) {
            try {
                $this->record->assignRole($this->roleToAssign);
                \Log::info('Role assigned successfully:', [
                    'user_email' => $this->record->email,
                    'role' => $this->roleToAssign
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to assign role:', [
                    'user_email' => $this->record->email,
                    'role' => $this->roleToAssign,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            \Log::warning('No role to assign for user: ' . $this->record->email);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
