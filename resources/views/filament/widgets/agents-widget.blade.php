<x-filament-widgets::widget class="w-full">
    <!-- Company Filter Dropdown -->
    <div style="margin-bottom: 1.5rem; border-radius: 0.5rem; background: linear-gradient(to right, #eff6ff, #ecf9f9); padding: 1rem; border: 1px solid #bfdbfe;">
        <div>
            <label for="company-filter" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                Select Company
            </label>
            <select 
                id="company-filter" 
                wire:model.live="selectedCompanyId"
                style="width: 100%; border-radius: 0.5rem; border: 2px solid #93c5fd; background-color: white; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 500; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.3s; cursor: pointer;"
                onmouseover="this.style.borderColor='#60a5fa'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.1)';"
                onmouseout="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)';"
                onblur="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
            >
                @if(auth()->user()?->hasRole('super_admin'))
                    <option value="0">All Companies</option>
                @endif
                @foreach($this->getCompanies() as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Agents List -->
    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; width: 100%; max-width: 100%;" id="agents-container">
        @forelse($this->getAgents() as $agent)
            @php
                $isOnCall = $this->agentOnCallStatus[$agent->id] ?? false;
            @endphp
            <div 
                style="width: calc(16.666% - 0.625rem); display: flex; align-items: flex-start; gap: 1.5rem; border: 3px solid {{ $isOnCall ? '#16a34a' : '#c1c1c1' }}; border-radius: 1.8rem;" 
                class="shadow p-4 transition-all duration-200 {{ $agent->is_logged_in ? 'bg-green-50' : 'bg-gray-50' }} agent-item" 
                data-agent-id="{{ $agent->id }}"
            >
                <!-- Left: User Avatar -->
                <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: center;">
                    <svg style="width: 2rem; height: 2rem; margin-top: 0.25rem; margin-left: 0.25rem; color: #2ba1ef;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 4.842C15.976 4.337 14.146 4 12 4c-2.145 0-3.976.337-5.5.842m11 0c3.021 1 4.835 2.66 5.5 3.658L20.5 11l-3-2V4.842zm-11 0c-3.021 1-4.835 2.66-5.5 3.658L3.5 11l3-2V4.842z"></path><path fill="currentColor" fill-rule="evenodd" d="M10 6a1 1 0 0 1 1 1v2h2V7a1 1 0 1 1 2 0v2.586l5.121 5.121A3 3 0 0 1 21 16.828V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-1.172a3 3 0 0 1 .879-2.12L9 9.585V7a1 1 0 0 1 1-1zm2 11a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" clip-rule="evenodd"></path></svg>
                    <!-- Status Dot -->
                    <span style="margin-top: 0.25rem;" class="flex h-2.5 w-2.5 rounded-full agent-status-dot {{ $agent->is_logged_in ? 'bg-green-500' : 'bg-green-400' }}"></span>
                </div>

                <!-- Right: Name and Extension -->
                <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 0.25rem;">
                    <!-- Name -->
                    <h3 class="font-semibold text-sm text-gray-900 truncate">{{ $agent->name }}</h3>
                    
                    <!-- Extension -->
                    @if($agent->extension)
                        <p class="text-xs text-gray-600">Ext: {{ $agent->extension }}</p>
                    @else
                        <p class="text-xs text-gray-400 text-center">-----</p>
                    @endif
                </div>
            </div>
        @empty
            <div style="width: 100%;" class="text-center py-8 text-gray-500">
                <p>No agents found for your company</p>
            </div>
        @endforelse
    </div>

    <!-- No additional script needed - polling is handled by app.js agent-polling module -->
</x-filament-widgets::widget>
