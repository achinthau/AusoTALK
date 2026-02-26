<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    private ?string $roleToAssign = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store role before it's removed from data
        $this->roleToAssign = $data['roles'] ?? null;

        \Log::info('CreateUser - Data received:', $data);
        
        // Handle auto-generate password
        if (!empty($data['auto_generate_password'])) {
            $data['password'] = \Str::random(12);
        }
        
        // If password is still not set, generate one
        if (empty($data['password'])) {
            $data['password'] = \Str::random(12);
        } else {
            // Hash the password if it was provided by user
            $data['password'] = bcrypt($data['password']);
        }

        // If company_admin user, auto-assign their company
        if (auth()->user()?->hasRole('company_admin') && auth()->user()?->company_id) {
            $data['company_id'] = auth()->user()->company_id;
        }

        // Remove non-database fields
        unset($data['roles']);
        unset($data['auto_generate_password']);
        unset($data['password_confirmation']); // Not a database field
        
        \Log::info('CreateUser - Data to save:', $data);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        \Log::info('CreateUser - After create:', [
            'user_id' => $this->record->id,
            'role' => $this->roleToAssign,
        ]);
        
        if (!empty($this->roleToAssign)) {
            try {
                $this->record->syncRoles([$this->roleToAssign]);
                app()['cache']->forget('spatie.permission.cache');
                
                \Log::info('CreateUser - Role assigned:', [
                    'user_id' => $this->record->id,
                    'role' => $this->roleToAssign
                ]);
            } catch (\Exception $e) {
                \Log::error('CreateUser - Failed to assign role:', [
                    'user_id' => $this->record->id,
                    'role' => $this->roleToAssign,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            \Log::warning('CreateUser - No role provided');
        }
    }

   /*  protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    } */
}
