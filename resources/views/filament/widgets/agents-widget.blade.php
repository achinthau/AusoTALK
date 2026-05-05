<x-filament-widgets::widget class="w-full">
    @if($this->isExpanded)
    <!-- Filters Section with Toggle Button -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: flex-start;">
        <!-- Super Admin: Company, Branch, and Department Filters -->
        @if(auth()->user()?->hasRole('super_admin'))
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; flex: 1;">
                <!-- Company Filter -->
                <div style="border-radius: 0.5rem; background: linear-gradient(to right, #eff6ff, #ecf9f9); padding: 0.5rem; border: 1px solid #bfdbfe; display: flex; align-items: center; gap: 0.5rem;">
                    <label for="company-filter" style="font-size: 0.875rem; font-weight: 600; color: #374151; white-space: nowrap;">
                        Company
                    </label>
                    <select 
                        id="company-filter" 
                        wire:model.live="selectedCompanyId"
                        style="flex: 1; border-radius: 0.5rem; border: 2px solid #93c5fd; background-color: white; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 500; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.3s; cursor: pointer;"
                        onmouseover="this.style.borderColor='#60a5fa'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.1)';"
                        onmouseout="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)';"
                        onblur="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                    >
                        <option value="0">All Companies</option>
                        @foreach($this->getCompanies() as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Branch Filter (for super admin) -->
                <div style="border-radius: 0.5rem; background: linear-gradient(to right, #eff6ff, #ecf9f9); padding: 0.5rem; border: 1px solid #bfdbfe; display: flex; align-items: center; gap: 0.5rem;">
                    <label for="branch-filter" style="font-size: 0.875rem; font-weight: 600; color: #374151; white-space: nowrap;">
                        Branch
                    </label>
                    <select 
                        id="branch-filter" 
                        wire:model.live="selectedBranchId"
                        style="flex: 1; border-radius: 0.5rem; border: 2px solid #93c5fd; background-color: white; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 500; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.3s; cursor: pointer;"
                        onmouseover="this.style.borderColor='#60a5fa'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.1)';"
                        onmouseout="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)';"
                        onblur="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                    >
                        <option value="">All Branches</option>
                        @foreach($this->getBranches() as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Department Filter -->
                <div style="border-radius: 0.5rem; background: linear-gradient(to right, #eff6ff, #ecf9f9); padding: 0.5rem; border: 1px solid #bfdbfe; display: flex; align-items: center; gap: 0.5rem;">
                    <label for="department-filter" style="font-size: 0.875rem; font-weight: 600; color: #374151; white-space: nowrap;">
                        Department
                    </label>
                    <select 
                        id="department-filter" 
                        wire:model.live="selectedDepartmentId"
                        style="flex: 1; border-radius: 0.5rem; border: 2px solid #93c5fd; background-color: white; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 500; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.3s; cursor: pointer;"
                        onmouseover="this.style.borderColor='#60a5fa'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.1)';"
                        onmouseout="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)';"
                        onblur="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                    >
                        <option value="">All Departments</option>
                        @foreach($this->getDepartments() as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @else
            <!-- Non-Super Admin: Branch and Department Filters -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; flex: 1;">
                @if($this->getBranches()->count() > 0)
                    <div style="border-radius: 0.5rem; background: linear-gradient(to right, #eff6ff, #ecf9f9); padding: 0.5rem; border: 1px solid #bfdbfe; display: flex; align-items: center; gap: 0.5rem;">
                        <label for="branch-filter" style="font-size: 0.875rem; font-weight: 600; color: #374151; white-space: nowrap;">
                            Select Branch
                        </label>
                        <select 
                            id="branch-filter" 
                            wire:model.live="selectedBranchId"
                            style="flex: 1; border-radius: 0.5rem; border: 2px solid #93c5fd; background-color: white; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 500; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.3s; cursor: pointer;"
                            onmouseover="this.style.borderColor='#60a5fa'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.1)';"
                            onmouseout="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)';"
                            onblur="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                        >
                            <option value="">All Branches</option>
                            @foreach($this->getBranches() as $branch)
                                <option value="{{ $branch->id }}" @selected($this->selectedBranchId === $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Department Filter -->
                <div style="border-radius: 0.5rem; background: linear-gradient(to right, #eff6ff, #ecf9f9); padding: 0.5rem; border: 1px solid #bfdbfe; display: flex; align-items: center; gap: 0.5rem;">
                    <label for="department-filter" style="font-size: 0.875rem; font-weight: 600; color: #374151; white-space: nowrap;">
                        Select Department
                    </label>
                    <select 
                        id="department-filter" 
                        wire:model.live="selectedDepartmentId"
                        style="flex: 1; border-radius: 0.5rem; border: 2px solid #93c5fd; background-color: white; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 500; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.3s; cursor: pointer;"
                        onmouseover="this.style.borderColor='#60a5fa'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.1)';"
                        onmouseout="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)';"
                        onblur="this.style.borderColor='#93c5fd'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';"
                    >
                        <option value="">All Departments</option>
                        @foreach($this->getDepartments() as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <!-- Toggle Button -->
        <button 
            wire:click="toggleExpand"
            style="background: transparent; border: none; color: #374151; padding: 0.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; transition: all 0.2s; flex-shrink: 0;"
        >
            <svg style="width: 1.5rem; height: 1.5rem; transform: {{ $this->isExpanded ? 'rotate(0deg)' : 'rotate(180deg)' }}; transition: transform 0.2s;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/>
            </svg>
        </button>
    </div>

    <!-- Agents Section -->
    <div style="margin-bottom: 2rem; margin-top: 1rem;">
        @forelse($this->getExtensionsByDepartment() as $agents)
            <!-- Agents in Department -->
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; width: 100%; max-width: 100%; margin-bottom: 1rem;">
                @forelse($agents as $agent)
                    @php
                        $status = $this->agentOnCallStatus[$agent->id] ?? ['on_call' => false, 'call_type' => 'primary', 'on_call_rec' => false, 'call_type_rec' => 'primary'];
                    @endphp
                    @if($agent->primary_extension)
                        @php
                            $isPrimaryOnCall = $status['on_call'] && $status['call_type'] === 'primary';
                            $isPrimaryOnCallRec = $status['on_call_rec'] && $status['call_type_rec'] === 'primary';
                        @endphp
                        <div 
                            x-data="{ showCallActions: false, isOnCall: {{ $isPrimaryOnCall || $isPrimaryOnCallRec ? 'true' : 'false' }} }"
                            style="width: calc(16.666% - 0.625rem); display: flex; align-items: flex-start; gap: 0.5rem; border: 3px solid {{ $isPrimaryOnCallRec ? '#dc2626' : ($isPrimaryOnCall ? '#16a34a' : '#c1c1c1') }}; border-radius: 1.8rem; position: relative;" 
                            class="shadow p-4 transition-all duration-200 {{ $agent->is_logged_in ? 'bg-green-50' : 'bg-gray-50' }} agent-item" 
                            data-agent-id="{{ $agent->id }}"
                            data-extension-type="primary"
                        >
                            <!-- Left: User Avatar -->
                            <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: center;">
                                <svg style="width: 2rem; height: 2rem; margin-top: 0.25rem; margin-left: 0.25rem; color: #2ba1ef;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 4.842C15.976 4.337 14.146 4 12 4c-2.145 0-3.976.337-5.5.842m11 0c3.021 1 4.835 2.66 5.5 3.658L20.5 11l-3-2V4.842zm-11 0c-3.021 1-4.835 2.66-5.5 3.658L3.5 11l3-2V4.842z"></path><path fill="currentColor" fill-rule="evenodd" d="M10 6a1 1 0 0 1 1 1v2h2V7a1 1 0 1 1 2 0v2.586l5.121 5.121A3 3 0 0 1 21 16.828V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-1.172a3 3 0 0 1 .879-2.12L9 9.585V7a1 1 0 0 1 1-1zm2 11a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" clip-rule="evenodd"></path></svg>
                                <!-- Status Dot -->
                                <span style="margin-top: 0.25rem;" class="flex h-2.5 w-2.5 rounded-full agent-status-dot {{ $agent->is_logged_in ? 'bg-green-500' : 'bg-green-400' }}"></span>
                            </div>

                            <!-- Middle: Name and Extension -->
                            <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 0.25rem;">
                                <!-- Name -->
                                <h3 style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; flex: 1; margin: 0;" class="font-semibold text-sm text-gray-900">{{ $agent->name }}</h3>
                                
                                <!-- Primary Extension -->
                                <p class="text-xs text-gray-600">{{ $agent->primary_extension }}</p>
                            </div>

                            <!-- Right: Ongoing Call Phone Icon (always in DOM, shown/hidden via JS) -->
                            <div x-show="isOnCall" x-cloak style="flex-shrink: 0; display: flex; flex-direction: column; align-items: center; position: relative;" class="call-actions-wrapper">
                                <!-- Phone Icon -->
                                <button 
                                    @click="showCallActions = !showCallActions"
                                    style="background: none; border: none; padding: 0.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                                    title="Ongoing call"
                                >
                                    <svg style="width: 1.25rem; height: 1.25rem;" class="text-green-600 hover:text-green-700 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"></path>
                                    </svg>
                                </button>

                                <!-- Call Action Icons Dropdown -->
                                <div 
                                    x-show="showCallActions" 
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95"
                                    @click.outside="showCallActions = false"
                                    style="position: absolute; top: 2rem; right: 0; z-index: 50; background: white; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15); padding: 0.5rem; display: flex; flex-direction: column; gap: 0.375rem; border: 1px solid #e5e7eb;"
                                >
                                    <!-- Listen -->
                                    <button 
                                        style="background: none; border: none; padding: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; transition: background-color 0.15s;"
                                        onmouseover="this.style.backgroundColor='#fef2f2'"
                                        onmouseout="this.style.backgroundColor='transparent'"
                                        title="Listen"
                                    >
                                        <svg style="width: 1.375rem; height: 1.375rem; color: #dc2626;" class="text-red-600" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 12H7C8.10457 12 9 12.8954 9 14V19C9 20.1046 8.10457 21 7 21H4C2.89543 21 2 20.1046 2 19V12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12V19C22 20.1046 21.1046 21 20 21H17C15.8954 21 15 20.1046 15 19V14C15 12.8954 15.8954 12 17 12H20C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12Z"></path>
                                        </svg>
                                    </button>

                                    <!-- Whisper -->
                                    <button 
                                        style="background: none; border: none; padding: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; transition: background-color 0.15s;"
                                        onmouseover="this.style.backgroundColor='#fff7ed'"
                                        onmouseout="this.style.backgroundColor='transparent'"
                                        title="Whisper"
                                    >
                                        <svg style="width: 1.375rem; height: 1.375rem; color: #fb923c;" class="text-orange-400" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2.5 7C2.5 9.20914 4.29086 11 6.5 11C8.70914 11 10.5 9.20914 10.5 7C10.5 4.79086 8.70914 3 6.5 3C4.29086 3 2.5 4.79086 2.5 7ZM2 21V16.5C2 14.0147 4.01472 12 6.5 12C8.98528 12 11 14.0147 11 16.5V21H2ZM17.5 11C15.2909 11 13.5 9.20914 13.5 7C13.5 4.79086 15.2909 3 17.5 3C19.7091 3 21.5 4.79086 21.5 7C21.5 9.20914 19.7091 11 17.5 11ZM13 21V16.5C13 14.0147 15.0147 12 17.5 12C19.9853 12 22 14.0147 22 16.5V21H13Z"></path>
                                        </svg>
                                    </button>

                                    <!-- Barge -->
                                    <button 
                                        style="background: none; border: none; padding: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; transition: background-color 0.15s;"
                                        onmouseover="this.style.backgroundColor='#f0fdf4'"
                                        onmouseout="this.style.backgroundColor='transparent'"
                                        title="Barge"
                                    >
                                        <svg style="width: 1.375rem; height: 1.375rem; color: #22c55e;" class="text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
                                            <path fill="currentColor" d="M5 16v-5.3c-0.6-0.3-1-1-1-1.7v-4c0-0.7 0.4-1.3 1-1.7 0-0.1 0-0.2 0-0.3 0-1.1-0.9-2-2-2s-2 0.9-2 2c0 1.1 0.9 2 2 2h-2c-0.5 0-1 0.5-1 1v4c0 0.5 0.5 1 1 1v5h4z"></path>
                                            <path fill="currentColor" d="M15 5h-2c1.1 0 2-0.9 2-2s-0.9-2-2-2-2 0.9-2 2c0 0.1 0 0.2 0 0.3 0.6 0.4 1 1 1 1.7v4c0 0.7-0.4 1.4-1 1.7v5.3h4v-5c0.5 0 1-0.5 1-1v-4c0-0.5-0.5-1-1-1z"></path>
                                            <path fill="currentColor" d="M10 2c0 1.105-0.895 2-2 2s-2-0.895-2-2c0-1.105 0.895-2 2-2s2 0.895 2 2z"></path>
                                            <path fill="currentColor" d="M10 4h-4c-0.5 0-1 0.5-1 1v4c0 0.5 0.5 1 1 1v6h4v-6c0.5 0 1-0.5 1-1v-4c0-0.5-0.5-1-1-1z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Secondary Extension Card -->
                    @if($agent->secondary_extension)
                    @php
                        $isSecondaryOnCall = $status['on_call'] && $status['call_type'] === 'secondary';
                        $isSecondaryOnCallRec = $status['on_call_rec'] && $status['call_type_rec'] === 'secondary';
                    @endphp
                    <div 
                        x-data="{ showCallActions: false, isOnCall: {{ $isSecondaryOnCall || $isSecondaryOnCallRec ? 'true' : 'false' }} }"
                        style="width: calc(16.666% - 0.625rem); display: flex; align-items: flex-start; gap: 0.5rem; border: 3px solid {{ $isSecondaryOnCallRec ? '#dc2626' : ($isSecondaryOnCall ? '#16a34a' : '#c1c1c1') }}; border-radius: 1.8rem; position: relative;" 
                        class="shadow p-4 transition-all duration-200 bg-gray-50 agent-item" 
                        data-agent-id="{{ $agent->id }}"
                        data-extension-type="secondary"
                    >
                        <!-- Left: Phone SVG -->
                        <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: center;">
                            <svg style="width: 2rem; height: 2rem; margin-top: 0.25rem; margin-left: 0.25rem; color: #104d76;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M15.5 1h-8C6.12 1 5 2.12 5 3.5v17C5 21.88 6.12 23 7.5 23h8c1.38 0 2.5-1.12 2.5-2.5v-17C18 2.12 16.88 1 15.5 1zm-4 21c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5-4H7V4h9v14z" fill="currentColor"></path>
                            </svg>
                            <!-- Status Dot -->
                            <span style="margin-top: 0.25rem;" class="flex h-2.5 w-2.5 rounded-full agent-status-dot bg-gray-400"></span>
                        </div>

                        <!-- Middle: Name and Secondary Extension -->
                        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 0.25rem;">
                            <!-- Name -->
                            <h3 style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; flex: 1; margin: 0;" class="font-semibold text-sm text-gray-900">{{ $agent->name }}</h3>
                            
                            <!-- Secondary Extension -->
                            <p class="text-xs text-gray-600">{{ $agent->secondary_extension }}</p>
                        </div>

                        <!-- Right: Ongoing Call Phone Icon for Secondary (always in DOM, shown/hidden via JS) -->
                        <div x-show="isOnCall" x-cloak style="flex-shrink: 0; display: flex; flex-direction: column; align-items: center; position: relative;" class="call-actions-wrapper">
                            <!-- Phone Icon -->
                            <button 
                                @click="showCallActions = !showCallActions"
                                style="background: none; border: none; padding: 0.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                                title="Ongoing call"
                            >
                                <svg style="width: 1.25rem; height: 1.25rem;" class="text-green-600 hover:text-green-700 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"></path>
                                </svg>
                            </button>

                            <!-- Call Action Icons Dropdown -->
                            <div 
                                x-show="showCallActions" 
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95"
                                @click.outside="showCallActions = false"
                                style="position: absolute; top: 2rem; right: 0; z-index: 50; background: white; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15); padding: 0.5rem; display: flex; flex-direction: column; gap: 0.375rem; border: 1px solid #e5e7eb;"
                            >
                                <!-- Listen -->
                                <button 
                                    style="background: none; border: none; padding: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; transition: background-color 0.15s;"
                                    onmouseover="this.style.backgroundColor='#fef2f2'"
                                    onmouseout="this.style.backgroundColor='transparent'"
                                    title="Listen"
                                >
                                    <svg style="width: 1.375rem; height: 1.375rem;" class="text-red-600" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 12H7C8.10457 12 9 12.8954 9 14V19C9 20.1046 8.10457 21 7 21H4C2.89543 21 2 20.1046 2 19V12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12V19C22 20.1046 21.1046 21 20 21H17C15.8954 21 15 20.1046 15 19V14C15 12.8954 15.8954 12 17 12H20C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12Z"></path>
                                    </svg>
                                </button>

                                <!-- Whisper -->
                                <button 
                                    style="background: none; border: none; padding: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; transition: background-color 0.15s;"
                                    onmouseover="this.style.backgroundColor='#fff7ed'"
                                    onmouseout="this.style.backgroundColor='transparent'"
                                    title="Whisper"
                                >
                                    <svg style="width: 1.375rem; height: 1.375rem;" class="text-orange-400" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.5 7C2.5 9.20914 4.29086 11 6.5 11C8.70914 11 10.5 9.20914 10.5 7C10.5 4.79086 8.70914 3 6.5 3C4.29086 3 2.5 4.79086 2.5 7ZM2 21V16.5C2 14.0147 4.01472 12 6.5 12C8.98528 12 11 14.0147 11 16.5V21H2ZM17.5 11C15.2909 11 13.5 9.20914 13.5 7C13.5 4.79086 15.2909 3 17.5 3C19.7091 3 21.5 4.79086 21.5 7C21.5 9.20914 19.7091 11 17.5 11ZM13 21V16.5C13 14.0147 15.0147 12 17.5 12C19.9853 12 22 14.0147 22 16.5V21H13Z"></path>
                                    </svg>
                                </button>

                                <!-- Barge -->
                                <button 
                                    style="background: none; border: none; padding: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; transition: background-color 0.15s;"
                                    onmouseover="this.style.backgroundColor='#f0fdf4'"
                                    onmouseout="this.style.backgroundColor='transparent'"
                                    title="Barge"
                                >
                                    <svg style="width: 1.375rem; height: 1.375rem;" class="text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
                                        <path fill="currentColor" d="M5 16v-5.3c-0.6-0.3-1-1-1-1.7v-4c0-0.7 0.4-1.3 1-1.7 0-0.1 0-0.2 0-0.3 0-1.1-0.9-2-2-2s-2 0.9-2 2c0 1.1 0.9 2 2 2h-2c-0.5 0-1 0.5-1 1v4c0 0.5 0.5 1 1 1v5h4z"></path>
                                        <path fill="currentColor" d="M15 5h-2c1.1 0 2-0.9 2-2s-0.9-2-2-2-2 0.9-2 2c0 0.1 0 0.2 0 0.3 0.6 0.4 1 1 1 1.7v4c0 0.7-0.4 1.4-1 1.7v5.3h4v-5c0.5 0 1-0.5 1-1v-4c0-0.5-0.5-1-1-1z"></path>
                                        <path fill="currentColor" d="M10 2c0 1.105-0.895 2-2 2s-2-0.895-2-2c0-1.105 0.895-2 2-2s2 0.895 2 2z"></path>
                                        <path fill="currentColor" d="M10 4h-4c-0.5 0-1 0.5-1 1v4c0 0.5 0.5 1 1 1v6h4v-6c0.5 0 1-0.5 1-1v-4c0-0.5-0.5-1-1-1z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div style="width: 100%; text-center py-4 text-gray-500;">
                        <p>No agents in this department</p>
                    </div>
                @endforelse
            </div>
        @empty
            <div style="text-center py-8 text-gray-500;">
                <p>No agents found</p>
            </div>
        @endforelse
    </div>

    <!-- No additional script needed - polling is handled by app.js agent-polling module -->
    @else
    <!-- Collapsed Header -->
    <div style="display: flex; align-items: center; justify-content: flex-end; padding: 0.5rem 0;">
        <button 
            wire:click="toggleExpand"
            style="background: transparent; border: none; color: #374151; padding: 0.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; transition: all 0.2s;"
        >
            <svg style="width: 1.5rem; height: 1.5rem; transform: {{ $this->isExpanded ? 'rotate(0deg)' : 'rotate(180deg)' }}; transition: transform 0.2s;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/>
            </svg>
        </button>
    </div>
    @endif
</x-filament-widgets::widget>
