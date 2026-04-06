<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Redis;

class AgentsWidget extends Widget
{
    protected static ?string $heading = 'Agents';

    protected static ?int $sort = 3;

    protected array|string|int $columnSpan = 'full';

    protected string $view = 'filament.widgets.agents-widget';

    public ?int $selectedCompanyId = null;

    public ?int $selectedBranchId = null;

    public ?int $selectedDepartmentId = null;

    /**
     * Store agent on-call status for reactive updates.
     * Values: false (not on call), 'primary', or 'secondary'
     *
     * @var array<int, false|string>
     */
    public array $agentOnCallStatus = [];

    public bool $isExpanded = true;

    public function mount(): void
    {
        // Set default to user's company
        if (auth()->user()?->company_id) {
            $this->selectedCompanyId = auth()->user()->company_id;
        }

        // Set default to user's branch if available
        if (auth()->user()?->branch_id) {
            $this->selectedBranchId = auth()->user()->branch_id;
        }

        $this->initializeAgentStatus();
    }

    /**
     * Initialize agent on-call status from Redis (batched for performance)
     */
    private function initializeAgentStatus(): void
    {
        $agents = $this->getAgents();
        $this->agentOnCallStatus = $this->batchGetAgentRedisStatuses($agents);
    }

    /**
     * Batch fetch agent on-call statuses from Redis to avoid N+1
     *
     * @param  Collection  $agents
     * @return array<int, false|string>
     */
    private function batchGetAgentRedisStatuses($agents): array
    {
        $statuses = [];

        if ($agents->isEmpty()) {
            return $statuses;
        }

        $redis = Redis::connection()->client();
        $redis->select(1);

        // Group agents by company to batch Redis calls
        $agentsByCompany = $agents->groupBy(function ($agent) {
            return $agent->company?->context;
        });

        foreach ($agentsByCompany as $context => $companyAgents) {
            if (! $context) {
                // Agents without company context
                foreach ($companyAgents as $agent) {
                    $statuses[$agent->id] = false;
                }

                continue;
            }

            // Fetch all Redis keys for this company's agents
            $keys = $companyAgents->map(fn ($agent) => "agent_on_call-{$context}-{$agent->id}")->toArray();
            $redisData = $redis->mget($keys);

            // Map results back to agent IDs
            foreach ($companyAgents as $index => $agent) {
                $callData = $redisData[$index] ?? null;
                if (! $callData) {
                    $statuses[$agent->id] = false;
                } else {
                    $decoded = json_decode($callData, true);
                    $statuses[$agent->id] = $decoded['type'] ?? 'primary';
                }
            }
        }

        return $statuses;
    }

    public function getCompanies(): Collection
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

    public function getBranches(): Collection
    {
        $user = auth()->user();

        // If selectedCompanyId is 0, it means "All Companies" is selected, so return all branches
        if ($this->selectedCompanyId === 0 || $this->selectedCompanyId === '0') {
            return Branch::orderBy('name')->get();
        }

        $companyId = $this->selectedCompanyId ?? $user?->company_id;

        if (! $companyId) {
            return Branch::whereNull('company_id')->orderBy('name')->get();
        }

        return Branch::where('company_id', $companyId)
            ->orderBy('name')
            ->get();
    }

    public function getAgents(): Collection
    {
        $user = auth()->user();

        // For non-super-admins, filter by branch and only users with extensions
        if (! $user?->hasRole('super_admin') && $this->selectedBranchId) {
            return User::query()
                ->where('branch_id', $this->selectedBranchId)
                ->where(function ($query) {
                    $query->whereNotNull('primary_extension')
                        ->orWhereNotNull('secondary_extension');
                })
                ->with(['company', 'department'])
                ->orderBy('primary_extension', 'asc')
                ->orderBy('secondary_extension', 'asc')
                ->get();
        }

        // For super admins, filter by company if selected and only users with extensions
        if ($user?->hasRole('super_admin')) {
            if ($this->selectedCompanyId === 0 || $this->selectedCompanyId === '0') {
                $companyId = null;
            } else {
                $companyId = $this->selectedCompanyId ?? $user->company_id;
            }

            return User::query()
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->where(function ($query) {
                    $query->whereNotNull('primary_extension')
                        ->orWhereNotNull('secondary_extension');
                })
                ->with(['company', 'department'])
                ->orderBy('primary_extension', 'asc')
                ->orderBy('secondary_extension', 'asc')
                ->get();
        }

        // Default to user's branch and only users with extensions
        if ($user?->branch_id) {
            return User::query()
                ->where('branch_id', $user->branch_id)
                ->where(function ($query) {
                    $query->whereNotNull('primary_extension')
                        ->orWhereNotNull('secondary_extension');
                })
                ->with(['company', 'department'])
                ->orderBy('primary_extension', 'asc')
                ->orderBy('secondary_extension', 'asc')
                ->get();
        }

        return User::query()->limit(0)->get();
    }

    public function getExtensionsByDepartment(): array
    {
        $user = auth()->user();

        // Determine which company_id to use for filtering
        $companyId = null;
        if ($user?->hasRole('super_admin')) {
            if ($this->selectedCompanyId === 0 || $this->selectedCompanyId === '0') {
                $companyId = null;
            } else {
                $companyId = $this->selectedCompanyId ?? $user->company_id;
            }
        } else {
            $companyId = $user?->company_id;
        }

        // Determine which branch_id to use for filtering
        $branchId = null;
        if ($this->selectedBranchId) {
            $branchId = $this->selectedBranchId;
        } elseif (! $user?->hasRole('super_admin') && $user?->branch_id) {
            $branchId = $user->branch_id;
        }

        // Get agents grouped by department
        $agentsQuery = User::query();

        if ($companyId) {
            $agentsQuery->where('company_id', $companyId);
        }

        if ($branchId) {
            $agentsQuery->where('branch_id', $branchId);
        }

        if ($this->selectedDepartmentId) {
            $agentsQuery->where('department_id', $this->selectedDepartmentId);
        }

        $agents = $agentsQuery
            ->with(['company', 'department'])
            ->orderByRaw('CAST(primary_extension AS UNSIGNED) ASC')
            ->orderByRaw('CAST(secondary_extension AS UNSIGNED) ASC')
            ->get();

        $result = [];

        foreach ($agents as $agent) {
            // Only include users with at least one extension
            if (empty($agent->primary_extension) && empty($agent->secondary_extension)) {
                continue;
            }
            $departmentName = $agent->department?->name ?? 'Unassigned';
            if (! isset($result[$departmentName])) {
                $result[$departmentName] = [];
            }
            $result[$departmentName][] = $agent;
        }

        // Sort departments alphabetically
        ksort($result);

        return $result;
    }

    public function getDepartments(): Collection
    {
        $user = auth()->user();

        // Determine which company_id to use for filtering
        $companyId = null;
        if ($user?->hasRole('super_admin')) {
            if ($this->selectedCompanyId === 0 || $this->selectedCompanyId === '0') {
                $companyId = null;
            } else {
                $companyId = $this->selectedCompanyId ?? $user->company_id;
            }
        } else {
            $companyId = $user?->company_id;
        }

        // Determine which branch_id to use for filtering
        $branchId = null;
        if ($this->selectedBranchId) {
            $branchId = $this->selectedBranchId;
        } elseif (! $user?->hasRole('super_admin') && $user?->branch_id) {
            $branchId = $user->branch_id;
        }

        // Get departments from agents
        $agentsQuery = User::query();

        if ($companyId) {
            $agentsQuery->where('company_id', $companyId);
        }

        if ($branchId) {
            $agentsQuery->where('branch_id', $branchId);
        }

        $departmentIds = $agentsQuery
            ->distinct()
            ->pluck('department_id')
            ->filter();

        $departments = Department::whereIn('id', $departmentIds)
            ->orderBy('name')
            ->get();

        return $departments;
    }

    /**
     * Check if an agent is currently on a call using Redis.
     *
     * @return false|string Returns false if not on call, or the call type ('primary'/'secondary')
     */
    public function isAgentOnCall(User $agent): false|string
    {
        $company = $agent->company;
        if (! $company) {
            return false;
        }

        $redis = Redis::connection()->client();
        $redis->select(1);

        $key = "agent_on_call-{$company->context}-{$agent->id}";
        $callData = $redis->get($key);

        if (! $callData) {
            return false;
        }

        $decoded = json_decode($callData, true);

        return $decoded['type'] ?? 'primary';
    }

    /**
     * Update agent status when call starts/ends (called from WebSocket)
     */
    public function updateAgentStatus(int $userId, bool $isOnCall): void
    {
        $this->agentOnCallStatus[$userId] = $isOnCall;
    }

    public function toggleExpand(): void
    {
        $this->isExpanded = ! $this->isExpanded;
    }
}
