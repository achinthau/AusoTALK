<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Company;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Checkbox::make('auto_generate_password')
                    ->label('Auto Generate Password')
                    ->live()
                    ->dehydrated(false),
                TextInput::make('password')
                    ->password()
                    ->required(function (string $operation, $get) {
                        if ($operation === 'create' && $get('auto_generate_password')) {
                            return false;
                        }
                        return $operation === 'create';
                    })
                    ->hidden(fn ($get) => $get('auto_generate_password'))
                    ->dehydrated(fn ($get) => !$get('auto_generate_password'))
                    ->maxLength(255),
                TextInput::make('password_confirmation')
                    ->password()
                    ->label('Confirm Password')
                    ->required(function (string $operation, $get) {
                        if ($operation === 'create' && $get('auto_generate_password')) {
                            return false;
                        }
                        return $operation === 'create';
                    })
                    ->hidden(fn ($get) => $get('auto_generate_password'))
                    ->maxLength(255),
                Select::make('roles')
                    ->label('Role')
                    ->options(function () {
                        $user = auth()->user();
                        
                        if ($user?->hasRole('company_admin')) {
                            // Company admin can only create company_admin or user roles
                            return [
                                'company_admin' => 'Company Admin',
                                'user' => 'User',
                            ];
                        }
                        
                        // Super admin can create any role
                        return [
                            'super_admin' => 'Super Admin',
                            'company_admin' => 'Company Admin',
                            'user' => 'User',
                        ];
                    })
                    ->required()
                    ->live()
                    ->dehydrated(false),
                Select::make('company_id')
                    ->label('Company')
                    ->options(Company::pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->required(function ($get) {
                        $role = $get('roles');
                        // Company is required for company_admin and user roles
                        return in_array($role, ['company_admin', 'user']);
                    })
                    ->hidden(function ($get) {
                        $user = auth()->user();
                        $role = $get('roles');
                        
                        // Hide for company_admin users (they can only create for their company)
                        if ($user?->hasRole('company_admin')) {
                            return true;
                        }
                        
                        // For super_admin, show only if role is company_admin or user
                        if (empty($role) || $role === 'super_admin') {
                            return true;
                        }
                        
                        return false;
                    }),
            ]);
    }
}
