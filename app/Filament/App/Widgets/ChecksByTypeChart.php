<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enums\CheckType;
use App\Models\Check;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

final class ChecksByTypeChart extends ChartWidget
{
    protected static ?string $heading = null;

    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return __('dashboard.widgets.checks_by_type');
    }

    protected function getData(): array
    {
        $httpCount = Check::query()
            ->where('user_id', Filament::auth()->id())
            ->where('type', CheckType::HTTP)
            ->count();

        $pingCount = Check::query()
            ->where('user_id', Filament::auth()->id())
            ->where('type', CheckType::PING)
            ->count();

        return [
            'datasets' => [
                [
                    'data' => [$httpCount, $pingCount],
                    'backgroundColor' => [
                        'rgb(59, 130, 246)', // Blue for HTTP
                        'rgb(168, 85, 247)', // Purple for PING
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(168, 85, 247)',
                    ],
                ],
            ],
            'labels' => [
                __('check_types.http'),
                __('check_types.ping'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
