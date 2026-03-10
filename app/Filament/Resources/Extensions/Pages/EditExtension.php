<?php

namespace App\Filament\Resources\Extensions\Pages;

use App\Filament\Resources\Extensions\ExtensionResource;
use App\Filament\Resources\Extensions\Schemas\ExtensionForm;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditExtension extends EditRecord
{
    protected static string $resource = ExtensionResource::class;

    public function form(Schema $schema): Schema
    {
        return ExtensionForm::configureEdit($schema);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Get all necessary data from current form data or fall back to record values
        $companyId = $data['company_id'] ?? $this->record->company_id;
        $number = $data['number'] ?? $this->record->number;
        $extensionTypeId = $data['extension_type_id'] ?? $this->record->extension_type_id;

        // Check for duplicate of the (company_id, number, extension_type_id) combination
        $exists = \App\Models\Extension::where('company_id', $companyId)
            ->where('number', $number)
            ->where('extension_type_id', $extensionTypeId)
            ->where('id', '!=', $this->record->id)
            ->exists();

        if ($exists) {
            Notification::make()
                ->danger()
                ->title('Duplicate Extension')
                ->body('This extension number and type combination already exists for this company.')
                ->persistent()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
