<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enums\CheckHistoryType;
use App\Models\Check;
use App\Models\CheckHistory;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class CheckStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = Filament::auth()->id();
        [$startDate, $endDate] = $this->getDateRange();

        $totalChecks = Check::where('user_id', $userId)->count();
        $activeChecks = Check::where('user_id', $userId)
            ->where('active', true)
            ->count();

        $checksInRange = CheckHistory::query()
            ->whereHas('check', function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->count();

        $failedChecksInRange = CheckHistory::query()
            ->whereHas('check', function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->where('type', CheckHistoryType::ERROR)
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->count();

        $successRate = $checksInRange > 0
            ? round((($checksInRange - $failedChecksInRange) / $checksInRange) * 100, 1)
            : 100;

        $avgResponseTime = CheckHistory::query()
            ->whereHas('check', function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->whereNotNull('metadata->transfer_time')
            ->avg('metadata->transfer_time');

        $rangeLabel = $this->getRangeLabel();

        return [
            Stat::make(__('dashboard.stats.total_checks'), $totalChecks)
                ->description($activeChecks.' '.__('dashboard.stats.active'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make(__('dashboard.stats.checks'), $checksInRange)
                ->description($failedChecksInRange.' '.__('dashboard.stats.failed').' '.$rangeLabel)
                ->descriptionIcon($failedChecksInRange > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check')
                ->color($failedChecksInRange > 0 ? 'warning' : 'success'),

            Stat::make(__('dashboard.stats.success_rate'), $successRate.'%')
                ->description($rangeLabel)
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($successRate >= 95 ? 'success' : ($successRate >= 80 ? 'warning' : 'danger')),

            Stat::make(__('dashboard.stats.avg_response_time'), $avgResponseTime ? round($avgResponseTime, 0).'ms' : __('dashboard.stats.n_a'))
                ->description($rangeLabel)
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
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

    private function getRangeLabel(): string
    {
        $dateRange = $this->filters['dateRange'] ?? '30d';
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        if ($dateRange === 'custom' && $startDate && $endDate) {
            return __('dashboard.range_labels.custom');
        }

        return match ($dateRange) {
            '24h' => __('dashboard.range_labels.24h'),
            '7d' => __('dashboard.range_labels.7d'),
            '90d' => __('dashboard.range_labels.90d'),
            default => __('dashboard.range_labels.30d'),
        };
    }
}
