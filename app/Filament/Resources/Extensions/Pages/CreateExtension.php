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
        $data['updatedby'] = $data['updatedby'] ?? (auth()->user()?->id ?? null);

        // Call the Auso API before creating the extension
        $apiData = [
            ['name' => 'extension', 'contents' => $data['number']],
            ['name' => 'password', 'contents' => $data['password']],
            ['name' => 'context', 'contents' => $data['context']],
            ['name' => 'status', 'contents' => '1'],
            ['name' => 'exten_type', 'contents' => $data['exten_type']],
            ['name' => 'type', 'contents' => $data['exten_type']],
            ['name' => 'updatedby', 'contents' => (string) $data['updatedby']],
        ];

        try {
            $response = (new AusoApiManager)->createExtension($apiData);

            // Only proceed if API returns 200
            if (($response['status'] ?? null) !== 200) {
                Notification::make()
                    ->danger()
                    ->title('API Error')
                    ->body('Failed to create extension. API returned status: '.($response['status'] ?? 'Unknown'))
                    ->persistent()
                    ->send();

                $this->halt();
            }

            // Store API response in data for reference
            $data['api_status'] = $response['status'] ?? null;
            $data['api_payload'] = $apiData;
            $data['api_response'] = $response;
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('API Error')
                ->body('Error creating extension: '.$e->getMessage())
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('Failed to create extension in Auso API', [
                'error' => $e->getMessage(),
            ]);

            $this->halt();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Store the API call details if available from form data
        $extension = $this->record;

        $updateData = [];

        if (isset($this->data['api_status'])) {
            $updateData['api_status'] = $this->data['api_status'];
        }
        if (isset($this->data['api_payload'])) {
            $updateData['api_payload'] = $this->data['api_payload'];
        }
        if (isset($this->data['api_response'])) {
            $updateData['api_response'] = $this->data['api_response'];
        }

        if (! empty($updateData)) {
            $extension->update($updateData);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
