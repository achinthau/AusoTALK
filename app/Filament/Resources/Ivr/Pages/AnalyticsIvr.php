<?php

namespace App\Filament\Resources\Ivr\Pages;

use App\Filament\Resources\Ivr\IvrResource;
use App\Models\AuIvrCall;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class AnalyticsIvr extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = IvrResource::class;

    protected string $view = 'filament.resources.ivr.pages.analytics-ivr';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = request()->query('startDate', now()->subDays(31)->toDateString());
        $this->endDate = request()->query('endDate', now()->toDateString());
    }

    // public function getHeaderActions(): array
    // {
    //     return [
    //         \Filament\Actions\Action::make('back')
    //             ->label('Back to Report')
    //             ->icon('heroicon-m-arrow-left')
    //             ->url(static::$resource::getUrl())
    //             ->color('gray'),
    //     ];
    // }

    public function getDnisData(): array
    {
        $dnisNumbers = AuIvrCall::distinct('dnis')
            ->pluck('dnis')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        $data = [];

        foreach ($dnisNumbers as $dnis) {
            $chartData = $this->getChartDataForDnis($dnis);
            $data[$dnis] = $chartData;
        }

        return $data;
    }

    private function getChartDataForDnis(string $dnis): array
    {
        $query = AuIvrCall::query()
            ->where('dnis', $dnis)
            ->whereBetween('date', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59'])
            ->leftJoin('cdr', 'au_ivr_calls.uniqueid', '=', 'cdr.uniqueid')
            ->selectRaw('DATE(au_ivr_calls.date) as date_only')
            ->selectRaw('count(au_ivr_calls.id) as total_calls')
            ->selectRaw('count(CASE WHEN cdr.disposition = "ANSWERED" THEN 1 END) as answered_calls')
            ->selectRaw('count(CASE WHEN cdr.disposition != "ANSWERED" OR cdr.disposition IS NULL THEN 1 END) as missed_calls')
            ->groupBy(DB::raw('DATE(au_ivr_calls.date)'))
            ->orderBy(DB::raw('DATE(au_ivr_calls.date)'))
            ->get();

        $labels = $query->pluck('date_only')->toArray();
        $totalCalls = $query->pluck('total_calls')->toArray();
        $answeredCalls = $query->pluck('answered_calls')->toArray();
        $missedCalls = $query->pluck('missed_calls')->toArray();

        return [
            'labels' => $labels,
            'totalCalls' => $totalCalls,
            'answeredCalls' => $answeredCalls,
            'missedCalls' => $missedCalls,
        ];
    }
}
