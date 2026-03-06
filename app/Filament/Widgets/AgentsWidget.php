<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Redis;

class AgentsWidget extends Widget
{
    protected static ?string $heading = 'Agents';

    protected static ?int $sort = 3;

    protected array|string|int $columnSpan = 'full';

    protected string $view = 'filament.widgets.agents-widget';

    public ?int $selectedCompanyId = null;

    /**
     * Store agent on-call status for reactive updates
     */
    public array $agentOnCallStatus = [];

    public function mount(): void
    {
        // Set default to user's company
        if (auth()->user()?->company_id) {
            $this->selectedCompanyId = auth()->user()->company_id;
        }

        $this->initializeAgentStatus();
    }

    /**
     * Initialize agent on-call status from Redis
     */
    private function initializeAgentStatus(): void
    {
        $this->agentOnCallStatus = [];
        foreach ($this->getAgents() as $agent) {
            $this->agentOnCallStatus[$agent->id] = $this->isAgentOnCall($agent);
        }
    }

    public function getCompanies(): \Illuminate\Database\Eloquent\Collection
    {
        $user = auth()->user();

        // Super admin can see all companies
        if ($user?->hasRole('super_admin')) {
            return Company::orderBy('name')->get();
        }

        // Company admin can only see their company
        if ($user?->hasRole('company_admin') && $user->company_id) {
            return Company::where('id', $user->company_id)->get();
        }

        // Regular users see their company
        if ($user->company_id) {
            return Company::where('id', $user->company_id)->get();
        }

        return collect();
    }

    public function getAgents(): \Illuminate\Database\Eloquent\Collection
    {
        // If "All" is selected (selectedCompanyId is 0 or null), show all agents
        // Otherwise, use selected company or default to user's company
        if ($this->selectedCompanyId === 0 || $this->selectedCompanyId === '0') {
            $companyId = null;
        } else {
            $companyId = $this->selectedCompanyId ?? auth()->user()->company_id;
        }

        return User::query()
            // ->whereHas('roles', fn ($query) => $query->where('name', 'agent'))
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if an agent is currently on a call using Redis.
     */
    public function isAgentOnCall(User $agent): bool
    {
        $company = $agent->company;
        if (! $company) {
            return false;
        }

        $redis = Redis::connection()->client();
        $redis->select(1);

        $key = "agent_on_call-{$company->context}-{$agent->id}";

        return (bool) $redis->exists($key);
    }

    /**
     * Update agent status when call starts/ends (called from WebSocket)
     */
    public function updateAgentStatus(int $userId, bool $isOnCall): void
    {
        $this->agentOnCallStatus[$userId] = $isOnCall;
    }
}
