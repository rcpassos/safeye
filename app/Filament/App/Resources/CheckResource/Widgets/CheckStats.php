<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CheckResource\Widgets;

use App\Enums\CheckHistoryType;
use App\Enums\CheckType;
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

        $lastCheckInfo = $check->latestChecks->first(); // Changed from ->last() to ->first() because ordered desc
        $lastCheck = $lastCheckInfo ? ($lastCheckInfo->type === CheckHistoryType::ERROR ? __('common.sickly') : __('common.healthy')) : __('common.n_a');

        $uptime = $check->latestChecks->count() > 0 ? ceil(($check->latestChecks->count() - $check->latestIssues->count()) * 100 / $check->latestChecks->count()) : null;

        // Calculate performance based on check type
        $successfulChecks = $check->latestChecks->filter(fn ($checkHistory): bool => $checkHistory->type === CheckHistoryType::SUCCESS);

        $performance = null;
        $performanceLabel = __('checks.performance');

        if ($check->type === CheckType::HTTP) {
            // HTTP: Show average response time
            $checksWithTransferTime = $successfulChecks->filter(fn ($checkHistory): bool => isset($checkHistory->metadata['transfer_time']));

            if ($checksWithTransferTime->count() > 0) {
                $totalTransferTime = $checksWithTransferTime->sum(fn ($checkHistory) => $checkHistory->metadata['transfer_time']);
                $performance = round($totalTransferTime / $checksWithTransferTime->count(), 3).'s';
            }
        } elseif ($check->type === CheckType::PING) {
            // PING: Show average ping time and packet loss
            $checksWithAvgTime = $successfulChecks->filter(fn ($checkHistory): bool => isset($checkHistory->metadata['avg_time']));

            if ($checksWithAvgTime->count() > 0) {
                $totalAvgTime = $checksWithAvgTime->sum(fn ($checkHistory) => $checkHistory->metadata['avg_time']);
                $avgPingTime = round($totalAvgTime / $checksWithAvgTime->count(), 1);

                // Also calculate average packet loss
                $checksWithPacketLoss = $successfulChecks->filter(fn ($checkHistory): bool => isset($checkHistory->metadata['packet_loss']));
                $avgPacketLoss = 0;

                if ($checksWithPacketLoss->count() > 0) {
                    $totalPacketLoss = $checksWithPacketLoss->sum(fn ($checkHistory) => $checkHistory->metadata['packet_loss']);
                    $avgPacketLoss = round($totalPacketLoss / $checksWithPacketLoss->count(), 1);
                }

                $performance = $avgPingTime.'ms';
                $performanceLabel = __('checks.avg_ping_time');
            }
        }

        return [
            Stat::make(__('checks.last_check'), $lastCheck)
                ->color($lastCheckInfo ? ($lastCheckInfo->type === CheckHistoryType::ERROR ? 'danger' : 'success') : null)
                ->description($lastCheckInfo?->created_at?->diffForHumans()),

            Stat::make(__('checks.uptime'), $uptime ? $uptime.'%' : __('common.n_a'))
                ->description($uptime ? __('common.24_hours') : null),

            Stat::make($performanceLabel, $performance ?? __('common.n_a'))
                ->description($performance !== null ? __('common.24_hours') : null),

            Stat::make(__('checks.checks_alerts'), $check->latestChecks->count() > 0 ? "{$check->latestChecks->count()} ({$check->latestIssues->count()})" : __('common.n_a'))
                ->description($check->latestChecks->count() > 0 ? __('common.24_hours') : null),
        ];
    }
}
