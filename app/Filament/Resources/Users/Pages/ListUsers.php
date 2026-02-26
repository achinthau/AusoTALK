<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modal()
                ->modalHeading('Create User')
                ->using(function (array $data) {
                    // Handle password
                    if (!empty($data['auto_generate_password'])) {
                        $data['password'] = \Str::random(12);
                    }
                    
                    if (empty($data['password'])) {
                        $data['password'] = \Str::random(12);
                    } else {
                        $data['password'] = bcrypt($data['password']);
                    }
                    
                    // If company_admin user, auto-assign their company
                    if (auth()->user()?->hasRole('company_admin') && auth()->user()?->company_id) {
                        $data['company_id'] = auth()->user()->company_id;
                    }
                    
                    // Store role for later assignment
                    $role = $data['roles'] ?? null;
                    
                    // Remove non-database fields
                    unset($data['roles']);
                    unset($data['auto_generate_password']);
                    unset($data['password_confirmation']);
                    
                    // Create the user
                    $user = $this->getResource()::getModel()::create($data);
                    
                    // Assign role
                    if ($role) {
                        $user->syncRoles([$role]);
                    }
                    
                    return $user;
                }),
        ];
    }
}
