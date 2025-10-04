<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Models\CheckHistory;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

final class CheckHistoryChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = null;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 2;

    public function getHeading(): string
    {
        return __('dashboard.widgets.check_history');
    }

    protected function getData(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        $data = CheckHistory::query()
            ->whereHas('check', function ($query): void {
                $query->where('user_id', Filament::auth()->id());
            })
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN type = "success" THEN 1 ELSE 0 END) as success_count'),
                DB::raw('SUM(CASE WHEN type = "error" THEN 1 ELSE 0 END) as error_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.chart.successful_checks'),
                    'data' => $data->pluck('success_count')->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'fill' => true,
                ],
                [
                    'label' => __('dashboard.chart.failed_checks'),
                    'data' => $data->pluck('error_count')->toArray(),
                    'backgroundColor' => 'rgba(244, 63, 94, 0.2)',
                    'borderColor' => 'rgb(244, 63, 94)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($date) => date('M j', strtotime($date)))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getDateRange(): array
    {
        $dateRange = $this->filters['dateRange'] ?? '30d';
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        if ($dateRange === 'custom' && $startDate && $endDate) {
            return [$startDate, $endDate];
        }

        return match ($dateRange) {
            '24h' => [now()->subHours(24), now()],
            '7d' => [now()->subDays(7), now()],
            '90d' => [now()->subDays(90), now()],
            default => [now()->subDays(30), now()], // 30d default
        };
    }
}
