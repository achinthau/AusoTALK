<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Date Range Selector -->
        <form method="GET" style="background: linear-gradient(to right, #eff6ff, #ecfdf5); border: 2px solid #a0bff2; border-radius: 12px; padding: 12px 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-wrap: nowrap; align-items: center; gap: 12px;">
            <div style="display: flex; flex-direction: column; gap: 4px; flex-shrink: 0;">
                <label for="startDate" style="color: #1e40af; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin: 0;">
                    📅 {{ __('Start Date') }}
                </label>
                <input 
                    type="date" 
                    id="startDate"
                    value="{{ $this->startDate }}"
                    onchange="updateDateRangeFromStart()"
                    style="border: 2px solid #60a5fa; background: white; border-radius: 6px; padding: 6px 10px; font-size: 13px; font-weight: 600; color: #111827; box-shadow: 0 1px 2px rgba(0,0,0,0.1); min-width: 140px;"
                />
            </div>
            <div style="display: flex; flex-direction: column; gap: 4px; flex-shrink: 0;">
                <label for="endDate" style="color: #1e40af; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin: 0;">
                    📅 {{ __('End Date') }}
                </label>
                <input 
                    type="date" 
                    id="endDate"
                    value="{{ $this->endDate }}"
                    onchange="updateDateRangeFromEnd()"
                    style="border: 2px solid #60a5fa; background: white; border-radius: 6px; padding: 6px 10px; font-size: 13px; font-weight: 600; color: #111827; box-shadow: 0 1px 2px rgba(0,0,0,0.1); min-width: 140px;"
                />
            </div>

            <div style="margin-left: auto;">
                <span style="background: rgba(59, 130, 246, 0.1); border: 2px solid #60a5fa; color: #1e40af; border-radius: 9999px; padding: 6px 12px; font-size: 10px; font-weight: bold; white-space: nowrap; display: inline-block; flex-shrink: 0;">
                    ⏱️ {{ __('Fixed 31-day window') }}
                </span>
            </div>
        </form>

        <!-- Charts for Each DNIS -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            @forelse ($this->getDnisData() as $dnis => $chartData)
                @if (count($chartData['labels']) > 0)
                    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border: 1px solid #f3f4f6; overflow: hidden; padding-top: 24px;">
                        <div style="background: rgba(249, 250, 251, 0.5); padding: 16px 24px; border-bottom: 1px solid #f3f4f6;">
                            <h3 style="font-size: 1.125rem; font-weight: bold; color: #111827;">
                                {{-- 📞 Destination: {{ $dnis }} --}}
                                📞  {{ $dnis }}
                            </h3>
                        </div>
                        <div style="padding: 24px;">
                            <canvas id="chart-{{ str_replace('.', '-', $dnis) }}" style="max-height: 400px; margin-bottom: 16px;"></canvas>
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const ctx = document.getElementById('chart-{{ str_replace('.', '-', $dnis) }}').getContext('2d');
                                
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: {!! json_encode($chartData['labels']) !!},
                                        datasets: [
                                            {
                                                label: 'Total Calls',
                                                data: {!! json_encode($chartData['totalCalls']) !!},
                                                borderColor: '#3b82f6',
                                                backgroundColor: '#3b82f6',
                                                fill: false,
                                                tension: 0.4,
                                            },
                                            {
                                                label: 'Answered',
                                                data: {!! json_encode($chartData['answeredCalls']) !!},
                                                borderColor: '#10b981',
                                                backgroundColor: '#10b981',
                                                fill: false,
                                                tension: 0.4,
                                            },
                                            {
                                                label: 'Missed',
                                                data: {!! json_encode($chartData['missedCalls']) !!},
                                                borderColor: '#ef4444',
                                                backgroundColor: '#ef4444',
                                                fill: false,
                                                tension: 0.4,
                                            },
                                        ],
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top',
                                            },
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                            },
                                        },
                                    },
                                });
                            });
                        </script>
                        </div>
                    </div>
                @endif
            @empty
                <div style="background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 0.75rem; padding: 32px; text-align: center;">
                    <p style="color: #6b7280; font-size: 14px;">
                        {{ __('No IVR data available') }}
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            const DAYS_GAP = 31;

            function addDays(dateStr, days) {
                const date = new Date(dateStr);
                date.setDate(date.getDate() + days);
                return date.toISOString().split('T')[0];
            }

            function subtractDays(dateStr, days) {
                const date = new Date(dateStr);
                date.setDate(date.getDate() - days);
                return date.toISOString().split('T')[0];
            }

            function updateDateRangeFromStart() {
                const startDate = document.getElementById('startDate').value;
                const endDate = addDays(startDate, DAYS_GAP);
                document.getElementById('endDate').value = endDate;
                navigateWithDates(startDate, endDate);
            }

            function updateDateRangeFromEnd() {
                const endDate = document.getElementById('endDate').value;
                const startDate = subtractDays(endDate, DAYS_GAP);
                document.getElementById('startDate').value = startDate;
                navigateWithDates(startDate, endDate);
            }

            function navigateWithDates(startDate, endDate) {
                const baseUrl = "{{ \App\Filament\Resources\Ivr\IvrResource::getUrl('analytics') }}";
                window.location.href = baseUrl + '?startDate=' + startDate + '&endDate=' + endDate;
            }
        </script>
    @endpush
</x-filament-panels::page>
