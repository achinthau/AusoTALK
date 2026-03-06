<?php

namespace App\Filament\Resources\Cdrs\Pages;

use App\Filament\Resources\Cdrs\CdrResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCdrs extends ListRecords
{
    protected static string $resource = CdrResource::class;

    protected ?string $heading = 'CDR Report';

    protected function modifyQueryWithFilters(Builder $query): Builder
    {
        $user = auth()->user();

        // Super admin can see all CDRs
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Regular users and company admins can only see CDRs from their company
        if ($user->company_id && $user->company?->context) {
            return $query->where('dcontext', $user->company->context);
        }

        // If no company context, return empty results
        return $query->whereRaw('1=0');
    }
}
