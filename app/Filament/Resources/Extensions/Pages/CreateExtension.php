<?php

namespace App\Filament\Resources\Extensions\Pages;

use App\Filament\Resources\Extensions\ExtensionResource;
use App\Services\AusoApiManager;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateExtension extends CreateRecord
{
    protected static string $resource = ExtensionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If company user (logged in user has company_id), force their company_id
        if (auth()->user()?->company_id) {
            $data['company_id'] = auth()->user()->company_id;
        }

        // Check for duplicate before attempting to create
        if (isset($data['company_id']) && isset($data['number'])) {
            $exists = \App\Models\Extension::where('company_id', $data['company_id'])
                ->where('number', $data['number'])
                ->exists();

            if ($exists) {
                Notification::make()
                    ->danger()
                    ->title('Duplicate Extension')
                    ->body('This extension number already exists for this company.')
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        // Populate context from company

        // Populate context from company
        if ($data['company_id'] ?? null) {
            $company = \App\Models\Company::find($data['company_id']);
            if ($company) {
                $data['context'] = $company->context ?? '';
            }
        }

        // Populate exten_type from extension type
        if ($data['extension_type_id'] ?? null) {
            $type = \App\Models\ExtensionType::find($data['extension_type_id']);
            if ($type) {
                $data['exten_type'] = $type->name ?? '';
            }
        }

        // Set defaults
        $data['status'] = $data['status'] ?? 'ACTIVE';
        $data['updatedby'] = $data['updatedby'] ?? (auth()->user()?->name ?? 'ADMIN');

        return $data;
    }

    protected function afterCreate(): void
    {
        // Call the Auso API to create the extension
        $extension = $this->record;

        $apiData = [
            ['name' => 'extension', 'contents' => $extension->number],
            ['name' => 'password', 'contents' => $extension->password],
            ['name' => 'context', 'contents' => $extension->context],
            ['name' => 'status', 'contents' => $extension->status],
            ['name' => 'exten_type', 'contents' => $extension->exten_type],
            ['name' => 'type', 'contents' => $extension->exten_type],
            ['name' => 'updatedby', 'contents' => $extension->updatedby],
        ];

        try {
            $response = (new AusoApiManager)->createExtension($apiData);

            // Store the API call details
            $extension->update([
                'api_status' => $response['status'] ?? null,
                'api_payload' => $apiData,
                'api_response' => $response,
            ]);
        } catch (\Exception $e) {
            // Store the failed attempt
            $extension->update([
                'api_status' => null,
                'api_payload' => $apiData,
                'api_response' => ['error' => $e->getMessage()],
            ]);

            // Log the error but don't prevent the record from being created
            \Illuminate\Support\Facades\Log::error('Failed to create extension in Auso API', [
                'extension_id' => $extension->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
