<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        \Log::info('=== mutateFormDataBeforeCreate CALLED ===', [
            'email' => $data['email'] ?? 'N/A',
            'roles' => $data['roles'] ?? 'N/A',
            'company_id' => $data['company_id'] ?? 'N/A',
            'auto_generate_password' => $data['auto_generate_password'] ?? false,
            'password_provided' => !empty($data['password']),
        ]);
        
        // Handle auto-generate password
        if (!empty($data['auto_generate_password'])) {
            // Generate a random password
            $data['password'] = \Str::random(12);
        }
        
        // If password is still not set, generate one
        if (empty($data['password'])) {
            $data['password'] = \Str::random(12);
        }
        
        // If company_admin user (doesn't have empty company_id), auto-assign their company
        if (auth()->user()?->hasRole('company_admin') && auth()->user()?->company_id) {
            $data['company_id'] = auth()->user()->company_id;
        }
        
        // Remove auto_generate_password from data - keep roles for afterCreate
        unset($data['auto_generate_password']);
        
        \Log::info('=== mutateFormDataBeforeCreate COMPLETED ===', [
            'final_data' => array_merge($data, ['password' => '***']),
        ]);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        \Log::info('=== afterCreate CALLED ===', [
            'record_exists' => isset($this->record),
            'record_email' => $this->record->email ?? 'N/A',
            'record_id' => $this->record->id ?? 'N/A',
        ]);
        
        // Get the role from the form data - it was submitted with the form
        $roleToAssign = request()->input('roles');
        
        \Log::info('After create - assigning role:', [
            'user_email' => $this->record->email,
            'roleToAssign' => $roleToAssign,
            'user_id' => $this->record->id,
            'request_all' => request()->all(),
        ]);
        
        if (!empty($roleToAssign)) {
            try {
                // Use the exact same approach as the seeder - syncRoles
                $this->record->syncRoles([$roleToAssign]);
                
                // Clear permission cache immediately
                app()['cache']->forget('spatie.permission.cache');
                
                \Log::info('Role assigned successfully:', [
                    'user_email' => $this->record->email,
                    'user_id' => $this->record->id,
                    'role' => $roleToAssign
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to assign role:', [
                    'user_email' => $this->record->email,
                    'user_id' => $this->record->id,
                    'role' => $roleToAssign,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            \Log::warning('No role to assign for user: ' . $this->record->email);
        }
    }

   /*  protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    } */
}
