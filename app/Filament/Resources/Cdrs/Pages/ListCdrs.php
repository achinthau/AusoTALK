<?php

namespace App\Filament\Resources\Cdrs\Pages;

use App\Filament\Resources\Cdrs\CdrResource;
use Filament\Resources\Pages\ListRecords;

class ListCdrs extends ListRecords
{
    protected static string $resource = CdrResource::class;

    protected ?string $heading = 'CDR Report';
}
