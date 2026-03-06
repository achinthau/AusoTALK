<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('domain')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->regex('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/')
                    ->validationMessages([
                        'regex' => 'Please enter a valid domain (e.g., example.com)',
                    ])
                    ->maxLength(255),
                TextInput::make('context')
                    ->label('Context')
                    ->placeholder('e.g., frominternalcompany')
                    ->nullable()
                    ->unique(ignoreRecord: true)
                    ->regex('/^[a-z0-9]+$/')
                    ->validationMessages([
                        'regex' => 'Context must only contain lowercase letters and numbers (no spaces or special characters).',
                        'unique' => 'This context value is already in use.',
                    ]),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->regex('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
                    ->unique(ignoreRecord: true)
                    ->nullable()
                    ->maxLength(255)
                    ->validationMessages([
                        'email' => 'Please enter a valid email address.',
                        'regex' => 'Please enter a valid email address (e.g., user@example.com).',
                        'unique' => 'This email is already in use.',
                    ]),
                TextInput::make('hotline')
                    ->label('Hotline')
                    ->inputMode('numeric')
                    ->nullable()
                    ->minLength(10)
                    ->maxLength(10)
                    ->regex('/^[0-9]{10}$/')
                    ->validationMessages([
                        'regex' => 'Hotline must be exactly 10 digits.',
                        'minLength' => 'Hotline must be at least 10 digits.',
                        'maxLength' => 'Hotline must not exceed 10 digits.',
                    ]),
            ]);
    }
}
