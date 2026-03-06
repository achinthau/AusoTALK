<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Company;
use App\Models\Extension;
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
                TextInput::make('phone')
                    ->label('Phone')
                    ->nullable()
                    ->inputMode('numeric')
                    ->minLength(10)
                    ->maxLength(10)
                    ->regex('/^[0-9]{10}$/')
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'min' => 'Phone number must be exactly 10 digits.',
                        'max' => 'Phone number must be exactly 10 digits.',
                        'regex' => 'Phone number must be exactly 10 numeric digits.',
                        'unique' => 'This phone number is already in use.',
                    ]),
                Select::make('extension')
                    ->label('Extension')
                    ->options(function ($get) {
                        $companyId = $get('company_id');
                        if (! $companyId) {
                            return [];
                        }

                        return Extension::where('company_id', $companyId)
                            ->where('status', 0)
                            ->pluck('number', 'number')
                            ->toArray();
                    })
                    ->searchable()
                    ->nullable()
                    ->live(),
                TextInput::make('nic')
                    ->label('NIC')
                    ->nullable()
                    ->maxLength(15),
                Select::make('gender')
                    ->label('Gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ])
                    ->nullable(),
                TextInput::make('address')
                    ->label('Address')
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->placeholder(function (string $operation) {
                        return $operation === 'edit' ? 'Leave blank to keep current password' : 'Password';
                    })
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
                TextInput::make('password_confirmation')
                    ->password()
                    ->label('Confirm Password')
                    ->required(fn (string $operation, $get) => ! empty($get('password')))
                    ->same('password')
                    ->validationMessages([
                        'same' => 'Passwords do not match.',
                    ])
                    ->dehydrated(false)
                    ->maxLength(255),
                Select::make('roles')
                    ->label('Role')
                    ->options(function () {
                        $user = auth()->user();

                        if ($user?->hasRole('company_admin')) {
                            // Company admin can only create company_admin, user, or agent roles
                            return [
                                'company_admin' => 'Company Admin',
                                'user' => 'User',
                                'agent' => 'Agent',
                            ];
                        }

                        // Super admin can create any role
                        return [
                            'super_admin' => 'Super Admin',
                            'company_admin' => 'Company Admin',
                            'user' => 'User',
                            'agent' => 'Agent',
                        ];
                    })
                    ->required()
                    ->dehydrated(true)
                    ->live()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        // Pre-fill with current role if editing
                        if ($record && empty($state)) {
                            $component->state($record->roles->first()?->name);
                        }
                    }),
                Select::make('company_id')
                    ->label('Company')
                    ->options(Company::pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->required(function ($get) {
                        $role = $get('roles');

                        // Company is required for company_admin, user, and agent roles
                        return in_array($role, ['company_admin', 'user', 'agent']);
                    })
                    ->hidden(function ($get) {
                        $user = auth()->user();
                        $role = $get('roles');

                        // Hide for company_admin users (they can only create for their company)
                        if ($user?->hasRole('company_admin')) {
                            return true;
                        }

                        // For super_admin, show only if role is company_admin, user, or agent
                        if (empty($role) || $role === 'super_admin') {
                            return true;
                        }

                        return false;
                    }),
            ]);
    }
}
