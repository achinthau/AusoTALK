<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Company;
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
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->maxLength(255),
                Select::make('company_id')
                    ->label('Company')
                    ->options(Company::pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->hidden(fn () => auth()->user()?->company_id !== null)
                    ->default(fn () => auth()->user()?->company_id),
                Select::make('roles')
                    ->label('Role')
                    ->options(fn ($get) => $get('company_id') 
                        ? ['user' => 'User']
                        : []
                    )
                    ->hidden(fn ($get) => empty($get('company_id')))
                    ->dehydrated(),
            ]);
    }
}
