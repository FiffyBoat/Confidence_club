<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        Gate::define('manage-users', fn ($user) => $user->is_active && $user->role === 'admin');
        Gate::define('manage-revenue', fn ($user) => $user->is_active && in_array($user->role, ['admin', 'treasurer'], true));
        Gate::define('view-reports', fn ($user) => $user->is_active && in_array($user->role, ['admin', 'treasurer'], true));
    }

}
