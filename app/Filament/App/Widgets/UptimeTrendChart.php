<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Models\CheckHistory;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

final class UptimeTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = null;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    public function getHeading(): string
    {
        return __('dashboard.widgets.uptime_trend');
    }

    protected function getData(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        // Get total checks and successful checks per day
        $data = CheckHistory::query()
            ->whereHas('check', function ($query): void {
                $query->where('user_id', Filament::auth()->id());
            })
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN type = "success" THEN 1 ELSE 0 END) as success_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate uptime percentage for each day
        $uptimeData = $data->map(function ($item): float|int {
            $total = (int) ($item->total ?? 0);
            $successCount = (int) ($item->success_count ?? 0);

            return $total > 0 ? round(($successCount / $total) * 100, 1) : 0;
        });

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.chart.uptime_percentage'),
                    'data' => $uptimeData->all(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($date): string => date('M j', strtotime((string) $date)))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'callback' => 'function(value) { return value + "%"; }',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
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
