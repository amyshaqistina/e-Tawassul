<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Note: Model-to-policy mappings are registered explicitly in
     * AppServiceProvider::boot() via Gate::policy() to keep multi-guard
     * authorization working with the custom guards (student/admin/nok/lecturer).
     */
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
