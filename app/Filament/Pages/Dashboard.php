<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CallStatisticsWidget;
use App\Filament\Widgets\DialerQueueWiseStatisticsWidget;
use App\Filament\Widgets\QueueWiseStatisticsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            CallStatisticsWidget::class,
            QueueWiseStatisticsWidget::class,
            DialerQueueWiseStatisticsWidget::class,
        ];
    }
}
