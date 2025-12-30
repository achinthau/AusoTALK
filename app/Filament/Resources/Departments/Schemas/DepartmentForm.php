<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Models\Branch;
use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Company')
                    ->options(Company::pluck('name', 'id'))
                    ->required()
                    ->live()
                    ->hidden(fn () => auth()->user()?->company_id !== null)
                    ->default(fn () => auth()->user()?->company_id),
                Select::make('branch_id')
                    ->label('Branch')
                    ->options(fn ($get) => Branch::where('company_id', $get('company_id'))
                        ->pluck('name', 'id')
                    )
                    ->preload()
                    ->searchable()
                    ->createOptionForm(fn ($form) => $form
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                        ])
                    )
                    ->createOptionUsing(function ($data, $get) {
                        return Branch::create([
                            'name' => $data['name'],
                            'company_id' => $get('company_id'),
                        ])->id;
                    }),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
