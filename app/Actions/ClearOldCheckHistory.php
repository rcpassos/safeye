<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CheckHistory;
use Carbon\Carbon;

final class ClearOldCheckHistory
{
    public function handle(int $retentionDays): int
    {
        if ($retentionDays <= 0) {
            return 0;
        }

        $cutoffDate = Carbon::now()->subDays($retentionDays);

        return CheckHistory::where('created_at', '<', $cutoffDate)->delete();
    }
}
