<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CallStatisticsWidget;
use App\Filament\Widgets\AgentsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = null;

    public function getWidgets(): array
    {
        return [
            CallStatisticsWidget::class,
            AgentsWidget::class,
        ];
    }
}
