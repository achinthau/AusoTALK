<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;

class AgentsWidget extends Widget
{
    protected static ?string $heading = 'Agents';

    protected static ?int $sort = 3;

    protected array|string|int $columnSpan = 'full';

    protected string $view = 'filament.widgets.agents-widget';

    public function getAgents(): \Illuminate\Database\Eloquent\Collection
    {
        $company = auth()->user()->company;

        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'agent'))
            ->where('company_id', $company?->id)
            ->orderBy('name')
            ->get();
    }
}
