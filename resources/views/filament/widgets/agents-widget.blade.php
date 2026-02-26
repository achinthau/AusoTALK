<x-filament-widgets::widget class="w-full">
    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; width: 100%; max-width: 100%;">
        @forelse($this->getAgents() as $agent)
            <div style="width: calc(16.666% - 0.625rem); display: flex; align-items: flex-start; gap: 1.5rem; border: 3px solid #c1c1c1; border-radius: 1.8rem;" class="shadow p-4 transition-all duration-200 {{ $agent->is_logged_in ? 'bg-green-50' : 'bg-gray-50' }}" data-agent-id="{{ $agent->id }}">
                <!-- Left: User Avatar -->
                <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: center;">
                    {{-- <svg style="width: 2.5rem; height: 2.5rem;" class="text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg> --}}
                    {{-- <svg style="width: 2.5rem; height: 2.5rem;" class="text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 1h-8C6.12 1 5 2.12 5 3.5v17C5 21.88 6.12 23 7.5 23h8c1.38 0 2.5-1.12 2.5-2.5v-17C18 2.12 16.88 1 15.5 1zm-4 21c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5-4H7V4h9v14z"></path></svg> --}}
                    <svg style="width: 2.5rem; height: 2.5rem; margin-top: 0.25rem; margin-left: 0.25rem; color: #2ba1ef;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 4.842C15.976 4.337 14.146 4 12 4c-2.145 0-3.976.337-5.5.842m11 0c3.021 1 4.835 2.66 5.5 3.658L20.5 11l-3-2V4.842zm-11 0c-3.021 1-4.835 2.66-5.5 3.658L3.5 11l3-2V4.842z"></path><path fill="currentColor" fill-rule="evenodd" d="M10 6a1 1 0 0 1 1 1v2h2V7a1 1 0 1 1 2 0v2.586l5.121 5.121A3 3 0 0 1 21 16.828V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-1.172a3 3 0 0 1 .879-2.12L9 9.585V7a1 1 0 0 1 1-1zm2 11a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" clip-rule="evenodd"></path></svg>
                    {{-- <svg style="width: 2.5rem; height: 2.5rem; margin-top: 0.25rem; margin-left: 0.25rem; color: #2ba1ef;" xmlns="http://www.w3.org/2000/svg" docname="user.svg" version="0.48.4 r9939" x="0px" y="0px" viewBox="0 0 1200 1200" enable-background="new 0 0 1200 1200" xml:space="preserve" fill="currentColor"><path id="path25031" connector-curvature="0" d="M939.574,858.383c-157.341-57.318-207.64-105.702-207.64-209.298 c0-62.17,51.555-102.462,69.128-155.744c17.575-53.283,27.741-116.367,36.191-162.256c8.451-45.889,11.809-63.638,16.404-112.532 C859.276,157.532,818.426,0,600,0C381.639,0,340.659,157.532,346.404,218.553c4.596,48.894,7.972,66.645,16.404,112.532 c8.433,45.888,18.5,108.969,36.063,162.256c17.562,53.286,69.19,93.574,69.19,155.744c0,103.596-50.298,151.979-207.638,209.298 C102.511,915.83,0,972.479,0,1012.5c0,39.957,0,187.5,0,187.5h1200c0,0,0-147.543,0-187.5S1097.426,915.894,939.574,858.383 L939.574,858.383z"></path></svg> --}}
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
                        <p class="text-xs text-gray-400">No extension</p>
                    @endif

                    <!-- Status Text -->
                    {{-- <span class="text-xs font-medium agent-status-text {{ $agent->is_logged_in ? 'text-green-700' : 'text-gray-600' }}">
                        {{ $agent->is_logged_in ? 'Online' : 'Offline' }}
                    </span> --}}
                </div>
            </div>
        @empty
            <div style="width: 100%;" class="text-center py-8 text-gray-500">
                <p>No agents found for your company</p>
            </div>
        @endforelse
    </div>
</x-filament-widgets::widget>
