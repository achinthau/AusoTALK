<?php

namespace App\Filament\Resources\Ivr\Widgets;

use App\Models\AuIvrCall;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class IvrAnalyticsChart extends LineChartWidget
{
    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = request()->query('startDate', null);
        $endDate = request()->query('endDate', null);

        $query = AuIvrCall::query()
            ->leftJoin('cdr', 'au_ivr_calls.uniqueid', '=', 'cdr.uniqueid')
            ->selectRaw('DATE(au_ivr_calls.date) as date_only')
            ->selectRaw('count(au_ivr_calls.id) as total_calls')
            ->selectRaw('count(CASE WHEN cdr.disposition = "ANSWERED" THEN 1 END) as answered_calls')
            ->selectRaw('count(CASE WHEN cdr.disposition != "ANSWERED" OR cdr.disposition IS NULL THEN 1 END) as missed_calls')
            ->groupBy(DB::raw('DATE(au_ivr_calls.date)'))
            ->orderBy(DB::raw('DATE(au_ivr_calls.date)'));

        if ($startDate && $endDate) {
            $query->whereBetween('au_ivr_calls.date', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        }

        $data = $query->get();

        $labels = $data->pluck('date_only')->toArray();
        $totalCalls = $data->pluck('total_calls')->toArray();
        $answeredCalls = $data->pluck('answered_calls')->toArray();
        $missedCalls = $data->pluck('missed_calls')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Calls',
                    'data' => $totalCalls,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f6',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Answered',
                    'data' => $answeredCalls,
                    'borderColor' => '#10b981',
                    'backgroundColor' => '#10b981',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Missed',
                    'data' => $missedCalls,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => '#ef4444',
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
