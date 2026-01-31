<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;


class EditCompanyForm
{
    public static function get(): array
    {
        return [
            Section::make('General Information')
                ->description('Company basic details')
                ->schema([
                    TextInput::make('name')
                        ->disabled()
                        ->dehydrated(false)
                        ->maxLength(255),
                    TextInput::make('domain')
                        ->disabled()
                        ->dehydrated(false)
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
                ]),
            Section::make('Contact Information')
                ->description('Email and communication details')
                ->schema([
                    TextInput::make('email')
                        ->email()
                        ->nullable()
                        ->unique('companies', 'email', ignoreRecord: true)
                        ->maxLength(255)
                        ->requiredIf('primary_email_enabled', true),
                    Checkbox::make('primary_email_enabled')
                        ->label('Use as primary communication email')
                        ->helperText('Enable this email as the primary contact method'),
                    TextInput::make('hotline')
                        ->maxLength(255),
                ]),
            Section::make('Extensions & Subscription')
                ->description('Extension types and concurrent channel configuration')
                ->schema([
                    TextInput::make('concurrent_channels')
                        ->label('Concurrent Channels')
                        ->numeric()
                        ->nullable()
                        ->helperText('Maximum number of concurrent calls allowed for this company')
                        ->inputMode('numeric')
                        ->minValue(0),
                    Select::make('extensionTypes')
                        ->label('Available Extension Types')
                        ->multiple()
                        ->relationship('extensionTypes', 'name')
                        ->preload()
                        ->searchable(),
                ])
                ->visible(fn () => auth()->user()?->hasRole('super_admin')),
        ];
    }
}
