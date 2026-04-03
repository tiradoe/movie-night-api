<?php

namespace App\Providers;

use App\Interfaces\MovieDbInterface;
use App\Models\User;
use App\Services\OmdbMovieService;
use Illuminate\Auth\Notifications\ResetPassword;
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
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return config('app.frontend_url')."/auth/reset-password/$token?email=".urlencode($user->email);
        });
    }
}
