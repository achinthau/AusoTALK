<x-filament-widgets::widget>
    <!-- Header with Toggle Button -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0;">Dialer Queue Statistics</h3>
        <button 
            wire:click="toggleExpand"
            style="background: #f3f4f6; border: 1px solid #d1d5db; color: #374151; padding: 0.5rem; border-radius: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; transition: all 0.2s;"
            onmouseover="this.style.backgroundColor='#e5e7eb'; this.style.borderColor='#9ca3af';"
            onmouseout="this.style.backgroundColor='#f3f4f6'; this.style.borderColor='#d1d5db';"
        >
            <svg style="width: 1.25rem; height: 1.25rem; transform: {{ $this->isExpanded ? 'rotate(0deg)' : 'rotate(180deg)' }}; transition: transform 0.2s;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/>
            </svg>
        </button>
    </div>

    @if($this->isExpanded)
    <div class="space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($this->getDialerQueueData() as $data)
                <div class="bg-white p-4 rounded-lg shadow border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-lg dark:text-white">{{ $data['queuename'] ?? $data->queuename }}</h4>
                        <svg class="w-6 h-6 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none" style="display: block; width: 24px; height: 24px; color: #22c55e;">
                            <path d="M19.44,13c-.22,0-.45-.07-.67-.12a9.44,9.44,0,0,1-1.31-.39,2,2,0,0,0-2.48,1l-.22.45a12.18,12.18,0,0,1-2.66-2,12.18,12.18,0,0,1-2-2.66L10.52,9a2,2,0,0,0,1-2.48,10.33,10.33,0,0,1-.39-1.31c-.05-.22-.09-.45-.12-.68a3,3,0,0,0-3-2.49h-3a3,3,0,0,0-3,3.41A19,19,0,0,0,18.53,21.91l.38,0a3,3,0,0,0,2-.76,3,3,0,0,0,1-2.25v-3A3,3,0,0,0,19.44,13Zm.5,6a1,1,0,0,1-.34.75,1.06,1.06,0,0,1-.82.25A17,17,0,0,1,4.07,5.22a1.09,1.09,0,0,1,.25-.82,1,1,0,0,1,.75-.34h3a1,1,0,0,1,1,.79q.06.41.15.81a11.12,11.12,0,0,0,.46,1.55l-1.4.65a1,1,0,0,0-.49,1.33,14.49,14.49,0,0,0,7,7,1,1,0,0,0,.76,0,1,1,0,0,0,.57-.52l.62-1.4a13.69,13.69,0,0,0,1.58.46q.4.09.81.15a1,1,0,0,1,.79,1ZM21.86,2.68a1,1,0,0,0-.54-.54,1,1,0,0,0-.38-.08h-4a1,1,0,1,0,0,2h1.58l-3.29,3.3a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l3.3-3.29V7.06a1,1,0,0,0,2,0v-4A1,1,0,0,0,21.86,2.68Z"></path>
                        </svg>
                    </div>
                    <hr class="mb-3 dark:border-gray-700">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between dark:text-gray-300">
                            <span class="text-gray-600 dark:text-gray-400">Dialed Count</span>
                            <span class="font-semibold dark:text-white">{{ $data['total_queue_count'] ?? $data->total_queue_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between dark:text-gray-300">
                            <span class="text-gray-600 dark:text-gray-400">Answered</span>
                            <span class="font-semibold dark:text-white">{{ $data['answered_count'] ?? $data->answered_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between dark:text-gray-300">
                            <span class="text-gray-600 dark:text-gray-400">Busy</span>
                            <span class="font-semibold dark:text-white">{{ $data['busy_count'] ?? $data->busy_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between dark:text-gray-300">
                            <span class="text-gray-600 dark:text-gray-400">Not Answered</span>
                            <span class="font-semibold dark:text-white">{{ $data['no_answer_count'] ?? $data->no_answer_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between dark:text-gray-300">
                            <span class="text-gray-600 dark:text-gray-400">Unreachable</span>
                            <span class="font-semibold dark:text-white">{{ $data['unreachable_count'] ?? $data->unreachable_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between dark:text-gray-300">
                            <span class="text-gray-600 dark:text-gray-400">Cancelled</span>
                            <span class="font-semibold dark:text-white">{{ $data['cancel_count'] ?? $data->cancel_count ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-gray-500 py-8">
                    No dialer queue data available
                </div>
            @endforelse
        </div>
    </div>
    @endif
</x-filament-widgets::widget>
