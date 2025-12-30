<?php

namespace App\Filament\Resources\Extensions\Schemas;

use App\Models\Company;
use App\Models\ExtensionType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExtensionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Company')
                    ->options(Company::pluck('name', 'id'))
                    ->required()
                    ->hidden(fn () => auth()->user()?->company_id !== null)
                    ->default(fn () => auth()->user()?->company_id),
                TextInput::make('number')
                    ->label('Extension Number')
                    ->placeholder('e.g., 1001')
                    ->required()
                    ->maxLength(4)
                    ->regex('/^\d{3,4}$/')
                    ->validationMessages([
                        'regex' => 'Extension number must be 3-4 digits.',
                    ]),
                Select::make('extension_type_id')
                    ->label('Extension Type')
                    ->options(ExtensionType::pluck('name', 'id'))
                    ->required()
                    ->preload()
                    ->searchable()
                    ->createOptionForm(fn ($form) => $form
                        ->schema([
                            TextInput::make('name')
                                ->label('Type Name')
                                ->placeholder('e.g., SIP, IAX2, PJSIP')
                                ->required()
                                ->maxLength(255)
                                ->unique('extension_types', 'name'),
                        ])
                    )
                    ->createOptionUsing(function ($data) {
                        return ExtensionType::create($data)->id;
                    }),
            ]);
    }
}
