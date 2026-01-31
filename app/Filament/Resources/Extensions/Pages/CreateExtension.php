<?php

namespace App\Filament\Resources\Extensions\Pages;

use App\Filament\Resources\Extensions\ExtensionResource;
use App\Services\AusoApiManager;
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
        return $data;
    }

    protected function afterCreate(): void
    {
        // Call the Auso API to create the extension
        $extension = $this->record;
        
        $apiData = [
            ['name' => 'extension', 'contents' => $extension->number],
            ['name' => 'password', 'contents' => $extension->password],
            ['name' => 'context', 'contents' => $extension->company->context],
            ['name' => 'status', 'contents' => 'ACTIVE'],
            ['name' => 'exten_type', 'contents' => $extension->extensionType->name],
            ['name' => 'type', 'contents' => $extension->extensionType->name],
            ['name' => 'updatedby', 'contents' => 'ADMIN'],
        ];

        try {
            $response = (new AusoApiManager())->createExtension($apiData);
            
            // Store the API call details
            $extension->update([
                'api_status' => 200,
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
