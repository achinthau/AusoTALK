<?php

namespace App\Filament\Resources\Extensions\Pages;

use App\Filament\Resources\Extensions\ExtensionResource;
use App\Services\AusoApiManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditExtension extends EditRecord
{
    protected static string $resource = ExtensionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Get company_id from data or fall back to record's company_id
        $companyId = $data['company_id'] ?? $this->record->company_id;
        $number = $data['number'] ?? $this->record->number;

        // Check for duplicate before attempting to save
        $exists = \App\Models\Extension::where('company_id', $companyId)
            ->where('number', $number)
            ->where('id', '!=', $this->record->id)
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

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction(),
            Action::make('resync')
                ->label('Resync with Auso')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => $this->record->api_status !== 200)
                ->action(fn () => $this->resyncExtension())
                ->requiresConfirmation()
                ->modalHeading('Resync Extension')
                ->modalDescription('This will send the extension data to the Auso API again.'),
        ];
    }

    protected function resyncExtension(): void
    {
        $extension = $this->record;
        $company = $extension->company;

        $apiData = [
            ['name' => 'extension', 'contents' => $extension->number],
            ['name' => 'password', 'contents' => $extension->password],
            ['name' => 'context', 'contents' => $company?->name ?? ''],
            ['name' => 'status', 'contents' => '1'],
            ['name' => 'exten_type', 'contents' => $extension->exten_type],
            ['name' => 'type', 'contents' => $extension->exten_type],
            ['name' => 'updatedby', 'contents' => (string) auth()->user()?->id],
        ];

        try {
            $response = (new AusoApiManager)->createExtension($apiData);

            // Store the API call details
            $extension->update([
                'api_status' => $response['status'] ?? null,
                'api_payload' => $apiData,
                'api_response' => $response,
            ]);

            if ($response['success'] ?? false) {
                Notification::make()
                    ->success()
                    ->title('Extension Synced')
                    ->body('The extension has been successfully synced with Auso API.')
                    ->send();
            } else {
                Notification::make()
                    ->warning()
                    ->title('API Response')
                    ->body('Extension data sent to API (Status: '.($response['status'] ?? 'Unknown').')')
                    ->send();
            }

            $this->refresh();
        } catch (\Exception $e) {
            // Store the failed attempt
            $extension->update([
                'api_status' => null,
                'api_payload' => $apiData,
                'api_response' => ['error' => $e->getMessage()],
            ]);

            Notification::make()
                ->danger()
                ->title('Sync Error')
                ->body('Error: '.$e->getMessage())
                ->send();

            \Illuminate\Support\Facades\Log::error('Failed to resync extension in Auso API', [
                'extension_id' => $extension->id,
                'error' => $e->getMessage(),
            ]);

            $this->refresh();
        }
    }
}
