<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal()
                ->mutateFormDataUsing(function (array $data): array {
                    \Log::info('=== CreateAction mutateFormDataUsing CALLED ===', [
                        'email' => $data['email'] ?? 'N/A',
                        'roles' => $data['roles'] ?? 'N/A',
                        'company_id' => $data['company_id'] ?? 'N/A',
                    ]);
                    
                    // Handle auto-generate password
                    if (!empty($data['auto_generate_password'])) {
                        $data['password'] = \Str::random(12);
                    }
                    
                    // If company_admin user, auto-assign their company
                    if (auth()->user()?->hasRole('company_admin') && auth()->user()?->company_id) {
                        $data['company_id'] = auth()->user()->company_id;
                    }
                    
                    unset($data['auto_generate_password']);
                    
                    return $data;
                })
                ->after(function ($record, array $data) {
                    \Log::info('=== CreateAction after CALLED ===', [
                        'record_id' => $record->id,
                        'record_email' => $record->email,
                        'form_data' => $data,
                    ]);
                    
                    // Get role from form data
                    $roleToAssign = $data['roles'] ?? null;
                    
                    \Log::info('Assigning role:', [
                        'roleToAssign' => $roleToAssign,
                    ]);
                    
                    if (!empty($roleToAssign)) {
                        try {
                            $record->syncRoles([$roleToAssign]);
                            app()['cache']->forget('spatie.permission.cache');
                            
                            \Log::info('Role assigned successfully:', [
                                'user_id' => $record->id,
                                'role' => $roleToAssign
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Failed to assign role:', [
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        \Log::warning('No role to assign');
                    }
                })
                ->successRedirectUrl(fn ($record) => UserResource::getUrl('edit', ['record' => $record])),
        ];
    }
}
