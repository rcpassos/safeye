<?php

declare(strict_types=1);

use App\Console\Commands\RunCheck;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__ . '/../routes/api.php',
        // commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // $schedule->command(RunCheck::class)->everyMinute();
        $schedule->command('app:clear-old-check-history')
            ->daily()
            ->at('02:00');
    })
    ->create();
