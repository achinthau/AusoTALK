<?php

namespace App\Filament\Resources\Extensions\Pages;

use App\Filament\Resources\Extensions\ExtensionResource;
use App\Services\AusoApiManager;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditExtension extends EditRecord
{
    protected static string $resource = ExtensionResource::class;

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
            
            // Store the successful API call details
            $extension->update([
                'api_status' => 200,
                'api_payload' => $apiData,
                'api_response' => $response,
            ]);

            Notification::make()
                ->success()
                ->title('Extension Synced')
                ->body('The extension has been successfully synced with Auso API.')
                ->send();

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
                ->title('Sync Failed')
                ->body('Error: ' . $e->getMessage())
                ->send();

            \Illuminate\Support\Facades\Log::error('Failed to resync extension in Auso API', [
                'extension_id' => $extension->id,
                'error' => $e->getMessage(),
            ]);

            $this->refresh();
        }
    }
}
