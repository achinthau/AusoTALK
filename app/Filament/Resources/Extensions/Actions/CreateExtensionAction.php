<?php

namespace App\Filament\Resources\Extensions\Actions;

use App\Services\AusoApiManager;
use Filament\Actions\CreateAction;

class CreateExtensionAction extends CreateAction
{
    public static function getDefaultName(): string
    {
        return 'create-extension';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->modalHeading('Create Extension')
            ->mutateFormDataUsing(function (array $data) {
                // If company user (logged in user has company_id), force their company_id
                if (auth()->user()?->company_id) {
                    $data['company_id'] = auth()->user()->company_id;
                }

                // Populate context from company
                if ($data['company_id'] ?? null) {
                    $company = \App\Models\Company::find($data['company_id']);
                    if ($company) {
                        $data['context'] = $company->name ?? '';
                    }
                }

                // Populate exten_type from extension type
                if ($data['extension_type_id'] ?? null) {
                    $type = \App\Models\ExtensionType::find($data['extension_type_id']);
                    if ($type) {
                        $data['exten_type'] = $type->name ?? '';
                    }
                }

                // Call the Auso API before creating the extension
                $apiData = [
                    ['name' => 'extension', 'contents' => $data['number']],
                    ['name' => 'password', 'contents' => $data['password']],
                    ['name' => 'context', 'contents' => $data['context']],
                    ['name' => 'status', 'contents' => '1'],
                    ['name' => 'exten_type', 'contents' => $data['exten_type']],
                    ['name' => 'updatedby', 'contents' => (string) (auth()->user()?->id ?? 'ADMIN')],
                ];

                try {
                    $response = (new AusoApiManager)->createExtension($apiData);

                    // Only proceed if API returns 200
                    if (($response['status'] ?? null) !== 200) {
                        throw new \Exception('API returned status: '.($response['status'] ?? 'Unknown'));
                    }

                    // Store API response in data for reference
                    $data['api_status'] = $response['status'] ?? null;
                    $data['api_payload'] = $apiData;
                    $data['api_response'] = $response;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to create extension in Auso API', [
                        'error' => $e->getMessage(),
                    ]);

                    throw new \Exception('Failed to create extension: '.$e->getMessage());
                }

                return $data;
            })
            ->after(function ($record) {
                // Store the API call details if available
                $extension = $record;

                // The API response data is already stored in the model during mutateFormDataUsing
                // This is now just for any post-processing if needed
            });
    }
}
