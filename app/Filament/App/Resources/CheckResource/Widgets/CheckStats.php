<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CheckResource\Widgets;

use App\Enums\CheckHistoryType;
use App\Models\Check;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class CheckStats extends BaseWidget
{
    use InteractsWithRecord;

    protected function getStats(): array
    {
        /** @var Check $check */
        $check = $this->getRecord();

        $lastCheckInfo = $check->latestChecks->last();
        $lastCheck = $lastCheckInfo ? ($lastCheckInfo->type === CheckHistoryType::ERROR ? __('common.sickly') : __('common.healthy')) : __('common.n_a');

        $uptime = $check->latestChecks->count() > 0 ? ceil($check->latestIssues->count() * 100 / $check->latestChecks->count()) : null;

        $performance = 0.0;
        foreach ($check->latestChecks as $checkHistory) {
            $performance += $checkHistory->metadata['transfer_time'] ?? 0.0; // TODO: check the units from transfer_time property
        }
        $performance = $check->latestChecks->count() ? round($performance / $check->latestChecks->count(), 1) : null;

        // TODO: to add more stats maybe I need to use sections in the ViewCheck page instead of Stats widget

        return [
            Stat::make(__('checks.last_check'), $lastCheck)
                ->color($lastCheckInfo ? ($lastCheckInfo->type === CheckHistoryType::ERROR ? 'danger' : 'success') : null)
                ->description($lastCheckInfo?->created_at?->diffForHumans()),

            Stat::make(__('checks.uptime'), $uptime ? $uptime.'%' : __('common.n_a'))
                ->description($uptime ? __('common.24_hours') : null),

            Stat::make(__('checks.performance'), $performance ? $performance.'s' : __('common.n_a'))
                ->description($performance ? __('common.24_hours') : null),

            Stat::make(__('checks.checks_alerts'), $check->latestChecks->count() > 0 ? "{$check->latestChecks->count()} ({$check->latestIssues->count()})" : __('common.n_a'))
                ->description($check->latestChecks->count() > 0 ? __('common.24_hours') : null),
        ];
    }
}
