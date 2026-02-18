<?php

namespace App\Providers;

use App\Interfaces\MovieDbInterface;
use App\Services\OmdbMovieService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MovieDbInterface::class, OmdbMovieService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
