<?php

namespace App\Filament\Resources\MissedCalls\Pages;

use App\Filament\Resources\MissedCalls\MissedCallsResource;
use Filament\Resources\Pages\ListRecords;

class ListMissedCalls extends ListRecords
{
    protected static string $resource = MissedCallsResource::class;

    protected ?string $heading = 'Missed Calls';
}
