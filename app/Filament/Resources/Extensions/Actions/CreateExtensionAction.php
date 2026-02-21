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

                return $data;
            })
            ->after(function ($record) {
                // Call the Auso API to create the extension
                $extension = $record;

                $apiData = [
                    ['name' => 'extension', 'contents' => $extension->number],
                    ['name' => 'password', 'contents' => $extension->password],
                    ['name' => 'context', 'contents' => $extension->context],
                    ['name' => 'status', 'contents' => $extension->status],
                    ['name' => 'exten_type', 'contents' => $extension->exten_type],
                    // ['name' => 'type', 'contents' => $extension->exten_type],
                    ['name' => 'updatedby', 'contents' => $extension->updatedby],
                ];
                // $apiData = [
                //     ['name' => 'extension', 'contents' => '3006'],
                //     ['name' => 'password', 'contents' => 'test1234'],
                //     ['name' => 'context', 'contents' => 'auso'],
                //     ['name' => 'status', 'contents' => '1'],
                //     ['name' => 'exten_type', 'contents' => 'pjsip'],
                //     ['name' => 'type', 'contents' => 'pjsip'],
                //     ['name' => 'updatedby', 'contents' => '1'],
                // ];

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
            });
    }
}
