<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\RunSingleCheck;
use App\Models\Check;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class RunCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run check for all the checks that are active';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $checks = Check::where('active', true)->get();
        $runSingleCheck = app(RunSingleCheck::class);

        foreach ($checks as $check) {
            $lastChecked = $check->last_run_at;
            $now = Carbon::now();

            if (is_null($lastChecked) || $lastChecked->diffInMinutes($now) >= $check->interval) {
                $runSingleCheck->execute($check);
            }
        }
    }
}
