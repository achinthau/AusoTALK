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
            ]);
    }
}
