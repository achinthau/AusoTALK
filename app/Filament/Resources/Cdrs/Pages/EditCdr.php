<?php

namespace App\Filament\Resources\Cdrs\Pages;

use App\Filament\Resources\Cdrs\CdrResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCdr extends EditRecord
{
    protected static string $resource = CdrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
