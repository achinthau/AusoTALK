<?php

namespace App\Filament\Resources\ExtensionTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExtensionTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Extension Type Name')
                    ->placeholder('e.g., SIP, IAX2, PJSIP')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Enter the protocol type name for extensions'),
            ]);
    }
}
