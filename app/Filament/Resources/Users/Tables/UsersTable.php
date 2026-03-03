<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('extension')
                    ->label('Extension')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nic')
                    ->label('NIC')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Gender')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'male' => 'info',
                            'female' => 'success',
                            'other' => 'warning',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'male' => 'Male',
                            'female' => 'Female',
                            'other' => 'Other',
                            default => $state,
                        };
                    }),
                TextColumn::make('address')
                    ->label('Address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modal()
                    ->modalHeading('Edit User')
                    ->using(function ($record, array $data) {
                        // Handle password
                        if (empty($data['password'])) {
                            unset($data['password']);
                        } else {
                            $data['password'] = bcrypt($data['password']);
                        }
                        
                        // Store role for later assignment
                        $role = $data['roles'] ?? null;
                        
                        // Remove non-database fields
                        unset($data['roles']);
                        unset($data['password_confirmation']);
                        
                        // Update the user
                        $record->update($data);
                        
                        // Assign role
                        if ($role) {
                            $record->syncRoles([$role]);
                        }
                        
                        return $record;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
