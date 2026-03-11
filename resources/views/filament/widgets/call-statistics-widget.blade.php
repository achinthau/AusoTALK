<x-filament-widgets::widget class="m-0 p-0" style="margin: 0 !important; padding: 0 !important; padding-bottom: 2rem !important; margin-top: -1.5rem !important;">
    <style>
        .fi-wi-widget {
            margin-top: -1.5rem !important;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-auto-rows: 1fr;
            gap: 1rem;
        }
        .stat-card {
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
            border: 1px solid #e5e7eb;
            padding: 1rem;
        }
        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .stat-card-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
        }
        .stat-card-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-top: auto;
        }
        .stat-card-sub {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        .stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            flex-shrink: 0;
        }
        .icon-slate  { background: #f1f5f9; }
        .icon-yellow { background: #fef9c3; }
        .icon-sky    { background: #e0f2fe; }
        .icon-red    { background: #fee2e2; }
        .icon-green  { background: #dcfce7; }
        .icon-orange { background: #ffedd5; }

        .dark .stat-card {
            background: #1f2937;
            border-color: #374151;
        }
        .dark .stat-card-label { color: #9ca3af; }
        .dark .stat-card-value { color: #f9fafb; }
        .dark .stat-card-sub   { color: #6b7280; }
        .dark .icon-slate  { background: #334155; }
        .dark .icon-yellow { background: #713f12; }
        .dark .icon-sky    { background: #0c4a6e; }
        .dark .icon-red    { background: #7f1d1d; }
        .dark .icon-green  { background: #14532d; }
        .dark .icon-orange { background: #7c2d12; }
    </style>

    <!-- Header with Toggle Button -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25;">
        <h3> </h3>
        <button 
            wire:click="toggleExpand"
            style="background: transparent; border: none; color: #374151; padding: 0; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem; transition: all 0.2s;"
        >
            <svg style="width: 1.5rem; height: 1.5rem; transform: {{ $this->isExpanded ? 'rotate(0deg)' : 'rotate(180deg)' }}; transition: transform 0.2s;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/>
            </svg>
        </button>
    </div>

    <!-- Content Section -->
    @if($this->isExpanded)
    <div class="stat-grid" x-data x-on:statistics-updated.window="$wire.refreshStats()">

        {{-- Row 1: Inbound, Outbound, Internal --}}

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-label">Inbound</span>
                <span class="stat-icon icon-sky">
                    <svg style="width:20px;height:20px;color:#38bdf8;flex-shrink:0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                        <path d="M19.44,13c-.22,0-.45-.07-.67-.12a9.44,9.44,0,0,1-1.31-.39,2,2,0,0,0-2.48,1l-.22.45a12.18,12.18,0,0,1-2.66-2,12.18,12.18,0,0,1-2-2.66L10.52,9a2,2,0,0,0,1-2.48,10.33,10.33,0,0,1-.39-1.31c-.05-.22-.09-.45-.12-.68a3,3,0,0,0-3-2.49h-3a3,3,0,0,0-3,3.41A19,19,0,0,0,18.53,21.91l.38,0a3,3,0,0,0,2-.76,3,3,0,0,0,1-2.25v-3A3,3,0,0,0,19.44,13Zm.5,6a1,1,0,0,1-.34.75,1.06,1.06,0,0,1-.82.25A17,17,0,0,1,4.07,5.22a1.09,1.09,0,0,1,.25-.82,1,1,0,0,1,.75-.34h3a1,1,0,0,1,1,.79q.06.41.15.81a11.12,11.12,0,0,0,.46,1.55l-1.4.65a1,1,0,0,0-.49,1.33,14.49,14.49,0,0,0,7,7,1,1,0,0,0,.76,0,1,1,0,0,0,.57-.52l.62-1.4a13.69,13.69,0,0,0,1.58.46q.4.09.81.15a1,1,0,0,1,.79,1ZM11,3H8V2A1,1,0,0,0,6,2V3H4A1,1,0,0,0,4,5H5V7A1,1,0,0,0,7,7V5H9a1,1,0,0,0,0-2Z"/>
                    </svg>
                </span>
            </div>
            <div class="stat-card-value">{{ $this->inboundCalls }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-label">Outbound</span>
                <span class="stat-icon icon-slate">
                    <svg style="width:20px;height:20px;color:#94a3b8;flex-shrink:0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                        <path d="M19.44,13c-.22,0-.45-.07-.67-.12a9.44,9.44,0,0,1-1.31-.39,2,2,0,0,0-2.48,1l-.22.45a12.18,12.18,0,0,1-2.66-2,12.18,12.18,0,0,1-2-2.66L10.52,9a2,2,0,0,0,1-2.48,10.33,10.33,0,0,1-.39-1.31c-.05-.22-.09-.45-.12-.68a3,3,0,0,0-3-2.49h-3a3,3,0,0,0-3,3.41A19,19,0,0,0,18.53,21.91l.38,0a3,3,0,0,0,2-.76,3,3,0,0,0,1-2.25v-3A3,3,0,0,0,19.44,13Zm.5,6a1,1,0,0,1-.34.75,1.06,1.06,0,0,1-.82.25A17,17,0,0,1,4.07,5.22a1.09,1.09,0,0,1,.25-.82,1,1,0,0,1,.75-.34h3a1,1,0,0,1,1,.79q.06.41.15.81a11.12,11.12,0,0,0,.46,1.55l-1.4.65a1,1,0,0,0-.49,1.33,14.49,14.49,0,0,0,7,7,1,1,0,0,0,.76,0,1,1,0,0,0,.57-.52l.62-1.4a13.69,13.69,0,0,0,1.58.46q.4.09.81.15a1,1,0,0,1,.79,1ZM21.86,2.68a1,1,0,0,0-.54-.54,1,1,0,0,0-.38-.08h-4a1,1,0,1,0,0,2h1.58l-3.29,3.3a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l3.3-3.29V7.06a1,1,0,0,0,2,0v-4A1,1,0,0,0,21.86,2.68Z"/>
                    </svg>
                </span>
            </div>
            <div class="stat-card-value">{{ $this->outboundCalls }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-label">Internal</span>
                <span class="stat-icon icon-yellow">
                    <svg style="width:20px;height:20px;color:#facc15;flex-shrink:0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                        <path d="M2,6A1,1,0,0,1,3,5H21a1,1,0,0,1,0,2H3A1,1,0,0,1,2,6Zm1,5H21a1,1,0,0,0,0-2H3a1,1,0,0,0,0,2Zm10,2H3a1,1,0,0,0,0,2H13a1,1,0,0,0,0-2Zm0,4H3a1,1,0,0,0,0,2H13a1,1,0,0,0,0-2Zm9.71-2.29-3-3a1,1,0,0,0-1.42,1.42L19.59,14,18.29,15.29a1,1,0,1,0,1.42,1.42l3-3A1,1,0,0,0,22.71,13.71Z"/>
                    </svg>
                </span>
            </div>
            <div class="stat-card-value">{{ $this->internalCalls }}</div>
        </div>

        {{-- Row 2: Answered, Ongoing, Abandoned --}}

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-label">Answered</span>
                <span class="stat-icon icon-green">
                    <svg style="width:20px;height:20px;color:#4ade80;flex-shrink:0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                        <path d="M19.44,13c-.22,0-.45-.07-.67-.12a9.44,9.44,0,0,1-1.31-.39,2,2,0,0,0-2.48,1l-.22.45a12.18,12.18,0,0,1-2.66-2,12.18,12.18,0,0,1-2-2.66L10.52,9a2,2,0,0,0,1-2.48,10.33,10.33,0,0,1-.39-1.31c-.05-.22-.09-.45-.12-.68a3,3,0,0,0-3-2.49h-3a3,3,0,0,0-3,3.41A19,19,0,0,0,18.53,21.91l.38,0a3,3,0,0,0,2-.76,3,3,0,0,0,1-2.25v-3A3,3,0,0,0,19.44,13Zm.5,6a1,1,0,0,1-.34.75,1.06,1.06,0,0,1-.82.25A17,17,0,0,1,4.07,5.22a1.09,1.09,0,0,1,.25-.82,1,1,0,0,1,.75-.34h3a1,1,0,0,1,1,.79q.06.41.15.81a11.12,11.12,0,0,0,.46,1.55l-1.4.65a1,1,0,0,0-.49,1.33,14.49,14.49,0,0,0,7,7,1,1,0,0,0,.76,0,1,1,0,0,0,.57-.52l.62-1.4a13.69,13.69,0,0,0,1.58.46q.4.09.81.15a1,1,0,0,1,.79,1ZM21,3H18V2a1,1,0,0,0-2,0V3H15a1,1,0,0,0,0,2h1V7a1,1,0,0,0,2,0V5h2a1,1,0,0,0,0-2Z"/>
                    </svg>
                </span>
            </div>
            <div class="stat-card-value">{{ $this->answeredCalls }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-label">Ongoing</span>
                <span class="stat-icon icon-orange">
                    <svg style="width:20px;height:20px;color:#fb923c;flex-shrink:0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                        <path d="M19.44,13c-.22,0-.45-.07-.67-.12a9.44,9.44,0,0,1-1.31-.39,2,2,0,0,0-2.48,1l-.22.45a12.18,12.18,0,0,1-2.66-2,12.18,12.18,0,0,1-2-2.66L10.52,9a2,2,0,0,0,1-2.48,10.33,10.33,0,0,1-.39-1.31c-.05-.22-.09-.45-.12-.68a3,3,0,0,0-3-2.49h-3a3,3,0,0,0-3,3.41A19,19,0,0,0,18.53,21.91l.38,0a3,3,0,0,0,2-.76,3,3,0,0,0,1-2.25v-3A3,3,0,0,0,19.44,13Zm.5,6a1,1,0,0,1-.34.75,1.06,1.06,0,0,1-.82.25A17,17,0,0,1,4.07,5.22a1.09,1.09,0,0,1,.25-.82,1,1,0,0,1,.75-.34h3a1,1,0,0,1,1,.79q.06.41.15.81a11.12,11.12,0,0,0,.46,1.55l-1.4.65a1,1,0,0,0-.49,1.33,14.49,14.49,0,0,0,7,7,1,1,0,0,0,.76,0,1,1,0,0,0,.57-.52l.62-1.4a13.69,13.69,0,0,0,1.58.46q.4.09.81.15a1,1,0,0,1,.79,1ZM21.86,2.68a1,1,0,0,0-.54-.54,1,1,0,0,0-.38-.08h-4a1,1,0,1,0,0,2h1.58l-3.29,3.3a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l3.3-3.29V7.06a1,1,0,0,0,2,0v-4A1,1,0,0,0,21.86,2.68Z"/>
                    </svg>
                </span>
            </div>
            <div class="stat-card-value">{{ $this->ongoingCalls }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-label">Abandoned</span>
                <span class="stat-icon icon-red">
                    <svg style="width:20px;height:20px;color:#f87171;flex-shrink:0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                        <path d="M19.44,13c-.22,0-.45-.07-.67-.12a9.44,9.44,0,0,1-1.31-.39,2,2,0,0,0-2.48,1l-.22.45a12.18,12.18,0,0,1-2.66-2,12.18,12.18,0,0,1-2-2.66L10.52,9a2,2,0,0,0,1-2.48,10.33,10.33,0,0,1-.39-1.31c-.05-.22-.09-.45-.12-.68a3,3,0,0,0-3-2.49h-3a3,3,0,0,0-3,3.41A19,19,0,0,0,18.53,21.91l.38,0a3,3,0,0,0,2-.76,3,3,0,0,0,1-2.25v-3A3,3,0,0,0,19.44,13Zm.5,6a1,1,0,0,1-.34.75,1.06,1.06,0,0,1-.82.25A17,17,0,0,1,4.07,5.22a1.09,1.09,0,0,1,.25-.82,1,1,0,0,1,.75-.34h3a1,1,0,0,1,1,.79q.06.41.15.81a11.12,11.12,0,0,0,.46,1.55l-1.4.65a1,1,0,0,0-.49,1.33,14.49,14.49,0,0,0,7,7,1,1,0,0,0,.76,0,1,1,0,0,0,.57-.52l.62-1.4a13.69,13.69,0,0,0,1.58.46q.4.09.81.15a1,1,0,0,1,.79,1ZM21.71,2.29a1,1,0,0,0-1.42,0L18,4.59,15.71,2.29a1,1,0,0,0-1.42,1.42L16.59,6,14.29,8.29a1,1,0,1,0,1.42,1.42L18,7.41l2.29,2.3a1,1,0,0,0,1.42-1.42L19.41,6l2.3-2.29a1,1,0,0,0,0-1.42Z"/>
                    </svg>
                </span>
            </div>
            <div class="stat-card-value">{{ $this->abandonedCalls }}</div>
        </div>

    </div>
    @endif
</x-filament-widgets::widget>
