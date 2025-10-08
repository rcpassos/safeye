<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enums\CheckHistoryType;
use App\Models\CheckHistory;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

final class SuccessRateChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = null;

    protected static ?int $sort = 5;

    public function getHeading(): string
    {
        return __('dashboard.widgets.success_rate');
    }

    protected function getData(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        $successCount = CheckHistory::query()
            ->whereHas('check', function ($query): void {
                $query->where('user_id', Filament::auth()->id());
            })
            ->where('type', CheckHistoryType::SUCCESS)
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->count();

        $errorCount = CheckHistory::query()
            ->whereHas('check', function ($query): void {
                $query->where('user_id', Filament::auth()->id());
            })
            ->where('type', CheckHistoryType::ERROR)
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->count();

        return [
            'datasets' => [
                [
                    'data' => [$successCount, $errorCount],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)', // Green for success
                        'rgb(239, 68, 68)', // Red for error
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                    ],
                ],
            ],
            'labels' => [
                __('dashboard.chart.successful_checks'),
                __('dashboard.chart.failed_checks'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
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

    private function getDateRange(): array
    {
        $dateRange = $this->filters['dateRange'] ?? '30d';
        $startDate = null;
        $endDate = null;

        if ($dateRange === 'custom') {
            $startDate = $this->filters['startDate'] ?? null;
            $endDate = $this->filters['endDate'] ?? null;
        } else {
            $days = match ($dateRange) {
                '24h' => 1,
                '7d' => 7,
                '30d' => 30,
                '90d' => 90,
                default => 30,
            };

            $startDate = now()->subDays($days)->startOfDay();
            $endDate = now()->endOfDay();
        }

        return [$startDate, $endDate];
    }
}
