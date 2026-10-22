<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\AuthEventSubscriber;
use App\Models\Vaccine;
use App\Observers\VaccineObserver;

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
        // Register auth event subscriber for activity logging
        Event::subscribe(AuthEventSubscriber::class);

        // Phase 2: backfill patient records when a vaccine is marked as standard
        Vaccine::observe(VaccineObserver::class);
    }
}
