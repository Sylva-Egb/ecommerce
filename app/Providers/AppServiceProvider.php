<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        // Forcer HTTPS en production
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        if(env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
