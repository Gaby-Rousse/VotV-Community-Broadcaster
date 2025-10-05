<?php

namespace App\Providers;

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
        //https://stackoverflow.com/questions/35827062/how-to-force-laravel-project-to-use-https-for-all-routes
        //J'ignore la production... car je suis poche
        URL::forceScheme('https');
    }
}
