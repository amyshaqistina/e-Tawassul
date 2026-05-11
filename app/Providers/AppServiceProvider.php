<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrapFive();

        // Register policies for multi-guard models.
        Gate::policy(\App\Models\CrisisReport::class, \App\Policies\CrisisReportPolicy::class);
        Gate::policy(\App\Models\Ldms::class,         \App\Policies\LDMSPolicy::class);
        Gate::policy(\App\Models\DeathConfirmation::class, \App\Policies\DeathConfirmationPolicy::class);
        Gate::policy(\App\Models\Student::class,      \App\Policies\StudentPolicy::class);
    }
}
