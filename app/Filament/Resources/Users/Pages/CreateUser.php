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

        // If company_admin user, auto-assign their company
        if (auth()->user()?->hasRole('company_admin') && auth()->user()?->company_id) {
            $data['company_id'] = auth()->user()->company_id;
        }

        // Remove non-database fields
        unset($data['roles']);
        unset($data['password_confirmation']); // Not a database field

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! empty($this->roleToAssign)) {
            $this->record->syncRoles([$this->roleToAssign]);
            app()['cache']->forget('spatie.permission.cache');
        }
    }

    /*  protected function getRedirectUrl(): string
     {
         return $this->getResource()::getUrl('edit', ['record' => $this->record]);
     } */
}
