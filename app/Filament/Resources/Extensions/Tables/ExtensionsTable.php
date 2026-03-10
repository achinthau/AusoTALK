<?php

namespace App\Filament\Resources\Extensions\Tables;

use App\Models\Company;
use App\Models\ExtensionType;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExtensionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Extension #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('extensionType.name')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modal()
                    ->form([
                        Select::make('company_id')
                            ->label('Company')
                            ->options(Company::pluck('name', 'id'))
                            ->required()
                            ->disabled(true)
                            ->columnSpan('full'),
                        TextInput::make('number')
                            ->label('Extension Number')
                            ->required()
                            ->disabled(true)
                            ->columnSpan(1),
                        Select::make('extension_type_id')
                            ->label('Extension Type')
                            ->options(ExtensionType::pluck('name', 'id'))
                            ->required()
                            ->disabled(true)
                            ->columnSpan(1),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->nullable()
                            ->helperText('Update to change password')
                            ->dehydrated()
                            ->columnSpan('full'),
                        Hidden::make('context'),
                        Hidden::make('status'),
                        Hidden::make('exten_type'),
                        Hidden::make('updatedby'),
                    ]),
            ])
            ->bulkActions([
                //
            ]);
    }
}
