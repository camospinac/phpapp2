<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\URL;
use App\Models\Subscription;
use App\Observers\SubscriptionObserver;

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
        // User::observe(UserObserver::class);
        Subscription::observe(SubscriptionObserver::class);
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
