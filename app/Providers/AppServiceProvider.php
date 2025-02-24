<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommands();
        $this->configureUrl();
    }

    private function configureCommands()
    {
        DB::prohibitDestructiveCommands(
            App::isProduction()
        );
    }

    private function configureUrl()
    {
        // force https (for local developement, use -> valet secure <project-name>)
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
