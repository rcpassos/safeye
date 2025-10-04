<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Models\CheckHistory;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

final class ResponseTimeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = null;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    public function getHeading(): string
    {
        return __('dashboard.widgets.response_time_trend');
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
            ->whereNotNull('metadata->transfer_time')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('AVG(CAST(json_extract(metadata, "$.transfer_time") AS REAL)) * 1000 as avg_response_time'),
                DB::raw('MIN(CAST(json_extract(metadata, "$.transfer_time") AS REAL)) * 1000 as min_response_time'),
                DB::raw('MAX(CAST(json_extract(metadata, "$.transfer_time") AS REAL)) * 1000 as max_response_time')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.chart.avg_response_time'),
                    'data' => $data->pluck('avg_response_time')->map(fn ($value): float => round((float) $value, 2))->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => __('dashboard.chart.max_response_time'),
                    'data' => $data->pluck('max_response_time')->map(fn ($value): float => round((float) $value, 2))->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderDash' => [5, 5],
                    'fill' => false,
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
                    'title' => [
                        'display' => true,
                        'text' => __('dashboard.chart.response_time_ms'),
                    ],
                ],
            ],
        ];
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
