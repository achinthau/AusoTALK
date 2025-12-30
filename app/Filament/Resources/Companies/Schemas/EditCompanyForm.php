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
                ]),
            Section::make('Contact Information')
                ->description('Email and communication details')
                ->schema([
                    TextInput::make('email')
                        ->email()
                        ->maxLength(255),
                    Checkbox::make('primary_email_enabled')
                        ->label('Use as primary communication email')
                        ->helperText('Enable this email as the primary contact method')
                        ->requiredIf('primary_email_enabled', true)
                        ->rules(['required_if:primary_email_enabled,1' => function ($context) {
                            return 'The email is required when using as primary communication email.';
                        }])
                        ->rules([
                            function ($context) {
                                return function ($attribute, $value, $fail) use ($context) {
                                    if ($value && empty($context['email'])) {
                                        $fail('Email is required to use this feature.');
                                    }
                                };
                            },
                        ]),
                    TextInput::make('hotline')
                        ->maxLength(255),
                ]),
        ];
    }
}
