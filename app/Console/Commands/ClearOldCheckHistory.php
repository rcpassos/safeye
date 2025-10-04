<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ClearOldCheckHistory as ClearOldCheckHistoryAction;
use Illuminate\Console\Command;

final class ClearOldCheckHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-old-check-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old check history records based on retention period';

    /**
     * Execute the console command.
     */
    public function handle(ClearOldCheckHistoryAction $action): int
    {
        $retentionDays = (int) config('app.check_history_retention_days', 30);

        $deletedCount = $action->handle($retentionDays);

        if ($deletedCount === 0 && $retentionDays <= 0) {
            $this->info('Check history retention is disabled (retention days is 0 or negative).');

            return self::SUCCESS;
        }

        if ($deletedCount > 0) {
            $this->info("Successfully deleted {$deletedCount} old check history record(s) older than {$retentionDays} days.");
        } else {
            $this->info('No old check history records found to delete.');
        }

        return self::SUCCESS;
    }
}
